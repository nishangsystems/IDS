<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Student\HomeController;
use App\Models\ApplicationForm;
use App\Models\Batch;
use App\Models\ClassSubject;
use App\Models\Config;
use App\Models\EntryQualification;
use App\Models\Level;
use App\Models\ProgramLevel;
use App\Models\School;
use App\Models\SchoolUnits;
use App\Models\StudentClass;
use App\Models\Students;
use App\Models\Subjects;
use App\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use SebastianBergmann\Environment\Console;

class ProgramController extends Controller
{

    public function sections()
    {
        $data['title'] = __('text.word_sections');
        $data['parent_id'] = 0;
        $data['units'] = \App\Models\SchoolUnits::where('parent_id', 0)->get();
        return view('admin.units.sections')->with($data);
    }

    public static function subunitsOf($id){
        $s_units = [];
        $direct_sub = DB::table('school_units')->where('parent_id', '=', $id)->get()->pluck('id')->toArray();
        $s_units[] = $id;
        if (count($direct_sub) > 0) {
            # code...
            foreach ($direct_sub as $sub) {
                # code...
                $s_units = array_merge_recursive($s_units, Self::subunitsOf($sub));
            }
        }
        return $s_units;
    }

    public static function orderedUnitsTree()
    {
        # code...
        $ids = DB::table('school_units')
                ->pluck('id')
                ->toArray();
        $units = [];
        $names = Self::allUnitNames();
        foreach ($ids as $id) {
            # code...
            foreach (Self::subunitsOf($id) as $sub) {
                # code...
                if (!in_array($sub, $units)) {
                    # code...
                    $units[$sub] = $names[$sub];
                }
            } 
        }
        return $units;
    }

    public static function allUnitNames()
    {
        # code...
        // added by Germanus. Loads listing of all classes accross all sections in a given school
        
        $base_units = DB::table('school_units')->get();
    
        // return $base_units;
        $listing = [];
        $separator = ' : ';
        foreach ($base_units as $key => $value) {
            # code...
            // set current parent as key and name as value, appending from the parent_array
            if (array_key_exists($value->parent_id, $listing)) {
                $listing[$value->id] = $listing[$value->parent_id] . $separator . $value->name; 
            }else {$listing[$value->id] = $value->name;}
    
            // atatch parent units if there be any
            if ($base_units->where('id', '=', $value->parent_id)->count() > 0) {
                // return $base_units->where('id', '=', $value->parent_id)->pluck('name')[0];
                $listing[$value->id] = array_key_exists($value->parent_id, $listing) ? 
                $listing[$value->parent_id] . $separator . $value->name :
                $base_units->where('id', '=', $value->parent_id)->pluck('name')[0] . $separator . $value->name ;
            }
            // if children are obove, move over and prepend to children listing
            foreach ($base_units->where('parent_id', '=', $value->id) as $keyi => $valuei) {
                $value->id > $valuei->id ?
                $listing[$valuei->id] = $listing[$value->id] . $separator . $listing[$value->id]:
                null;
            }
        }
        return $listing;
    }

    public function index($parent_id)
    {
        $data = [];
        $parent = \App\Models\SchoolUnits::find($parent_id);
        if (!$parent) {
            return  redirect(route('admin.sections'));
        }
        $units =  $parent->unit;
        $name = $parent->name;
        $data['title'] = ($units->count() == 0) ? __('text.no_sub_units_available_in', ['parent'=>$name]) : __('text.word_all').' '.$units->first()->type->name . " > {$name}";
        $data['units']  = $units;
        $data['parent_id']  = $parent_id;
        return view('admin.units.index')->with($data);
    }

    public function show($parent_id)
    {
        $data = [];
        $parent = \App\Models\SchoolUnits::find($parent_id);
        if (!$parent) {
            return  redirect(route('admin.sections'));
        }
        $units =  $parent->unit();
        $data['title'] = ($units->count() == 0) ? "No Sub Units Available in " . $parent->name : "All " . $units->first()->type->name;
        $data['units']  = $units;
        $data['parent_id']  = $parent_id;
        return view('admin.units.show')->with($data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'type' => 'required',
        ]);
        DB::beginTransaction();
        try {
            $unit = new \App\Models\SchoolUnits();
            $unit->name = $request->input('name');
            $unit->unit_id = $request->input('type');
            $unit->parent_id = $request->input('parent_id');
            $unit->prefix = $request->input('prefix');
            $unit->suffix = $request->input('suffix');
            $unit->save();
            DB::commit();
            return redirect()->to(route('admin.units.index', [$unit->parent_id]))->with('success', __('text.word_done'));
        } catch (\Exception $e) {
            DB::rollback();
            echo ($e);
        }
    }

    public function edit(Request $request, $id)
    {
        $lang = !$request->lang ? 'en' : $request->lang;
        \App::setLocale($lang);
        $data['id'] = $id;
        $unit = \App\Models\SchoolUnits::find($id);
        $data['unit'] = $unit;
        $data['parent_id'] = \App\Models\SchoolUnits::find($id)->parent_id;
        $data['title'] = __('text.word_edit')." " . $unit->name;
        return view('admin.units.edit')->with($data);
    }

    public function create(Request $request, $parent_id)
    {
        $data['parent_id'] = $parent_id;
        $parent = \App\Models\SchoolUnits::find($parent_id);
        $data['title'] = $parent ? __('text.new_sub_unit_under', ['item'=>$parent->name]) : __('text.new_section');
        return view('admin.units.create')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'type' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $unit = \App\Models\SchoolUnits::find($id);
            $unit->name = $request->input('name');
            $unit->unit_id = $request->input('type');
            $unit->prefix = $request->input('prefix');
            $unit->suffix = $request->input('suffix');
            $unit->parent_id = $request->input('parent_id');
            $unit->save();
            DB::commit();

            return redirect()->to(route('admin.units.index', [$unit->parent_id]))->with('success', __('text.word_done'));
        } catch (\Exception $e) {
            DB::rollback();
            echo ($e);
        }
    }

    /**
     * Delete the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $slug
     * @return \Illuminate\Http\Response
     */
    public function destroy($slug)
    {
        $unit = \App\Models\SchoolUnits::find($slug);
        if ($unit->unit->count() > 0) {
            return redirect()->back()->with('error', __('text.operation_not_allowed'));
        }
        $unit->delete();
        return redirect()->back()->with('success', __('text.word_done'));
    }


    // Request contains $program_id as $parent_id and $level_id
    public function subjects($program_level_id)
    {
        $parent = ProgramLevel::find($program_level_id);
        $data['title'] = __('text.subjects_under', ['class'=>$parent->name()]);
        $data['parent'] = $parent;
        // dd($parent->subjects()->get());
        $data['subjects'] = ProgramLevel::find($program_level_id)->subjects()->get();
        return view('admin.units.subjects')->with($data);
    }

    public function manageSubjects($parent_id)
    {
        $parent = ProgramLevel::find($parent_id);
        $data['parent'] = $parent;
        // return $parent;
        
        $data['title'] = __('text.manage_subjects_under', ['class'=>$parent->name()]);
        return view('admin.units.manage_subjects')->with($data);
    }

    public function students($id)
    { 
        return $this->studentsListing($id);

        $parent = \App\Models\SchoolUnits::find($id);
        $data['parent'] = $parent;

        $data['title'] = __('text.manage_students_under', ['unit'=>$parent->name]);
        return view('admin.units.student')->with($data);
    }
    public function studentsListing($id)
    {
    # code...
    // get array of ids of all sub units
    $year = \App\Helpers\Helpers::instance()->getCurrentAccademicYear();
    $subUnits = $this->subunitsOf($id);

    $students = DB::table('student_classes')
            ->whereIn('class_id', $subUnits)
            ->join('students', 'students.id', '=', 'student_classes.student_id')
            ->get();
    $parent = ProgramLevel::find($id);
    $data['parent'] = $parent;
    $data['students'] = $students;
    // dd($parent);
    $data['classes'] = \App\Http\Controllers\Admin\StudentController::baseClasses();
    $data['title'] = __('text.manage_students_under', ['unit'=>$parent->program()->first()->name]);
    return view('admin.units.student-listing')->with($data);
    }

    public function saveSubjects(Request  $request, $id)
    {
        $pl = ProgramLevel::find(request('parent_id'));
        $class_subjects = [];
        $validator = Validator::make($request->all(), [
            'subjects' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        $parent = $pl;

        $new_subjects = $request->subjects;
        // if($parent != null)
        foreach ($parent->subjects()->get() as $subject) {
            array_push($class_subjects, $subject->subject_id);
        }


        foreach ($new_subjects as $subject) {
            if (!in_array($subject, $class_subjects)) {
                if(\App\Models\ClassSubject::where('class_id', $pl->id)->where('subject_id', $subject)->count()>0){
                    continue;
                }
                \App\Models\ClassSubject::create([
                    'class_id' => $pl->id,
                    'subject_id' => $subject,
                    'status'=> \App\Models\Subjects::find($subject)->status,
                    'coef'=> \App\Models\Subjects::find($subject)->coef
                ]);
            }
        }

        foreach ($class_subjects as $k => $subject) {
            if (!in_array($subject, $new_subjects)) {
                ClassSubject::where('class_id', $pl->id)->where('subject_id', $subject)->count() > 0 ?
                ClassSubject::where('class_id', $pl->id)->where('subject_id', $subject)->first()->delete() : null;
            }
        }


        $data['title'] = __('text.manage_subjects_under', ['class'=>$parent->name()]);
        return redirect()->back()->with('success', __('text.word_done'));
    }

    public function getSubUnits($parent_id)
    {
        $data = SchoolUnits::where('parent_id', $parent_id)->get();
        return response()->json($data);
    }

    public function semesters($background_id)
    {
        # code...
        $data['title'] = __('text.manage_semesters_under', ['unit'=>\App\Models\SchoolUnits::find($background_id)->name]);
        $data['semesters'] = \App\Models\SchoolUnits::find($background_id)->semesters()->get();
        return view('admin.semesters.index')->with($data);
    }

    public function create_semester($background_id)
    {
        # code...
        $data['title'] = __('text.create_semesters_under', ['unit'=>\App\Models\SchoolUnits::find($background_id)->name]);
        $data['semesters'] = \App\Models\SchoolUnits::find($background_id)->semesters()->get();
        return view('admin.semesters.create')->with($data);
    }

    public function edit_semester($background_id, $id)
    {
        # code...
        $data['title'] = __('text.edit_semester');
        $data['semesters'] = \App\Models\SchoolUnits::find($background_id)->semesters()->get();
        $data['semester'] = \App\Models\Semester::find($id);
        return view('admin.semesters.edit');
    }

    public function store_semester($program_id, Request $request)
    {
        # code...
        $validator = Validator::make($request->all(), [
            'program_id'=>'required',
            'name'=>'required',
        ]);

        if ($validator->fails()) {
            # code...
            return back()->with('error', $validator->errors()->first());
        }
        try {
            //code...
            if (\App\Models\SchoolUnits::find($program_id)->semesters()->where('name', $request->name)->first()) {
                # code...
                return back()->with('error', __('text.record_already_exist', ['item'=>$request->name]));
            }
            $semester = new \App\Models\Semester($request->all());
            $semester->save();
            return back()->with('success', __('text.word_done'));
        } catch (\Throwable $th) {
            //throw $th;
            return back()->with('error', $th->getMessage());
        }
    }

    public function update_semester($program_id, $id)
    {
        # code...
    }

    public function delete_semester($id)
    {
        # code...
    }

    public function set_program_semester_type($program_id)
    {
        # code...
        $data['title'] = __('text.set_semester_type_for', ['unit'=>\App\Models\SchoolUnits::find($program_id)->name]);
        $data['semester_types'] = \App\Models\SemesterType::all();
        return view('admin.semesters.set_type', $data);
    }

    public function post_program_semester_type($program_id, Request $request)
    {
        # code...
        $validator = Validator::make(
            $request->all(),
            ['program_id'=>'required', 'background_id'=>'required']
        );

        if ($validator->fails()) {
            # code...
            return back()->with('error', $validator->errors()->first());
        }
        $program = \App\Models\SchoolUnits::find($program_id);
        $program->background_id = $request->background_id;
        $program->save();
        return back()->with('success', __('text.word_done'));
    }

    public function assign_program_level()
    {
        $data['title'] = __('text.manage_program_levels');
        return view('admin.units.set-levels', $data);
    }

    public function store_program_level(Request $request)
    {
        $this->validate($request, [
            'program_id'=>'required',
            'levels'=>'required'
        ]);
        // return $request->all();

        foreach ($request->levels as $key => $lev) {
            if (ProgramLevel::where('program_id', $request->program_id)->where('level_id', $lev)->count() == 0) {
                ProgramLevel::create(['program_id'=>$request->program_id, 'level_id'=>$lev]);
            }
        }
        return back()->with('success', __('text.word_done'));
    }

    public function program_levels($id)
    {
        $data['title'] = __('text.program_levels_for', ['unit'=>\App\Models\SchoolUnits::find($id)->name]);
        $data['program_levels'] =  ProgramLevel::where('program_id', $id)->pluck('level_id')->toArray();
        // $data['program_levels'] =  DB::table('school_units')->where('school_units.id', '=', $id)
        //             ->join('program_levels', 'program_id', '=', 'school_units.id')
        //             ->join('levels', 'levels.id', '=', 'program_levels.level_id')
        //             ->get(['program_levels.*', 'school_units.name as program', 'levels.level as level']);
        // dd($data);
        return view('admin.units.program-levels', $data);
    }


    public function program_index()
    {
        # code...
        $data['title'] = __('text.manage_programs');
        $data['programs'] = \App\Models\SchoolUnits::where('unit_id', 4)->get();
        // dd($data);
        return view('admin.units.programs', $data);
    }

    public function add_program_level($id, $level_id)
    {
        # code...
        if (ProgramLevel::where('program_id', $id)->where('level_id', $level_id)->count()>0) {
            # code...
            return back()->with('error', __('text.level_not_in_program'));
        }
        $pl = new ProgramLevel(['program_id'=>$id, 'level_id'=>$level_id]);
        $pl->save();
        return back()->with('success', __('text.word_done'));
    }

    public function _drop_program_level($id)
    {
        # code...
        if (ProgramLevel::find($id)==null) {
            # code...
            return back()->with('error', __('text.level_not_in_program'));
        }

        ProgramLevel::find($id)->delete();
        return back()->with('success', __('text.word_done'));
        
    }

    public function drop_program_level($id, $level_id)
    {
        # code...
        if (ProgramLevel::where('program_id', $id)->where('level_id', $level_id)->count()==0) {
            # code...
            return back()->with('error', __('text.level_not_in_program'));
        }
        ProgramLevel::where('program_id', $id)->where('level_id', $level_id)->first()->delete();
        return back()->with('success', __('text.word_done'));
        
    }

    public function program_levels_list()
    {
        # code...
        $data['title'] = __('text.class_list_for', ['campus'=>request()->has('campus_id') ? \App\Models\Campus::find(request('campus_id'))->name : '', 'class'=>request()->has('id') ? ProgramLevel::find(request('id'))->name() : '', 'year'=>request('year_id') != null ? Batch::find(request('year_id'))->name : '']);
        return view('admin.student.class_list', $data);
    }

    public function program_levels_list_index(Request $request)
    {
        # code...
        $data['title'] = __('text.student_listing');
        $data['filter'] = $request->filter ?? null;
        $data['items'] = [];
        if ($request->filter != null) {
            # code...
            switch ($request->filter) {
                case 'SCHOOL':
                    # code...
                    $schools = SchoolUnits::where(['unit_id'=>1])->get();
                    foreach ($schools as $key => $value) {
                        # code...
                        $data['items'][] = ['id'=>$value->id, 'name'=>$value->name];
                    }
                    return view('admin.student.student_list_index', $data);
                    break;
                    
                case 'FACULTY':
                    # code...
                    $faculties = SchoolUnits::where(['unit_id'=>2])->get();
                    foreach ($faculties as $key => $value) {
                        # code...
                        $data['items'][] = ['id'=>$value->id, 'name'=>$value->name];
                    }
                    return view('admin.student.student_list_index', $data);
                    break;
                        
                case 'DEPARTMENT':
                    # code...
                    $departments = SchoolUnits::where(['unit_id'=>3])->get();
                    foreach ($departments as $key => $value) {
                        $data['items'][] = ['id'=>$value->id, 'name'=>$value->name];
                        # code...
                    }
                    return view('admin.student.student_list_index', $data);
                    break;
                
                case 'PROGRAM':
                    # code...
                    $programs = SchoolUnits::where(['unit_id'=>4])->get();
                    // dd($programs);
                    foreach ($programs as $key => $value) {
                        $data['items'][] = ['id'=>$value->id, 'name'=>$value->name];
                        # code...
                    }
                    return view('admin.student.student_list_index', $data);
                    break;
                
                case 'CLASS':
                    # code...
                    $classes = Controller::sorted_program_levels();
                    foreach ($classes as $key => $value) {
                        $data['items'][] = ['id'=>$value['id'], 'name'=>$value['name']];
                        # code...
                    }
                    return view('admin.student.student_list_index', $data);
                    break;
                
                case 'LEVEL':
                    # code...
                    $levels = Level::all();
                    foreach ($levels as $key => $value) {
                        $data['items'][] = ['id'=>$value->id, 'name'=>'Level '.$value->level];
                        # code...
                    }
                    return view('admin.student.student_list_index', $data);
                    break;
                    
                    default:
                    # code...
                    break;
                }
            }
            // dd($data);
            return view('admin.student.student_list_index', $data);
    }

    public function bulk_program_levels_list(Request $request)
    {
        $year = $request->year_id ?? Helpers::instance()->getCurrentAccademicYear();
        # code...
        switch($request->filter){
            case 'SCHOOL':
                $data['title'] = __('text.students_for_school_of', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $programs = SchoolUnits::where(['school_units.unit_id'=>1])->where(['school_units.id'=>$request->item_id])
                        // ->join('school_units as faculties', ['faculties.parent_id'=>'school_units.id'])->where(['faculties.unit_id'=>2])
                        ->join('school_units as departments', ['departments.parent_id'=>'school_units.id'])->where(['departments.unit_id'=>3])
                        ->join('school_units as programs', ['programs.parent_id'=>'departments.id'])->where(['programs.unit_id'=>4])
                        ->pluck('programs.id')->toArray();
                $classes = ProgramLevel::whereIn('program_id', $programs)->pluck('id')->toArray();
                $students = Students::where(function($q){
                                auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);
                            })->where('students.active', true)
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->get(['students.*', 'student_classes.class_id as class_id']);
                // dd($students);
                $data['students'] = $students;
                return view('admin.student.bulk_list', $data);
                break;
            
            case 'FACULTY' :
                $data['title'] = __('text.students_for_faculty_of', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $programs = SchoolUnits::where(['school_units.unit_id'=>2])->where(['school_units.id'=>$request->item_id])
                        // ->join('school_units as faculties', ['faculties.parent_id'=>'school_units.id'])->where(['faculties.unit_id'=>2])
                        ->join('school_units as departments', ['departments.parent_id'=>'school_units.id'])->where(['departments.unit_id'=>3])
                        ->join('school_units as programs', ['programs.parent_id'=>'departments.id'])->where(['programs.unit_id'=>4])
                        ->pluck('programs.id')->toArray();
                $classes = ProgramLevel::whereIn('program_id', $programs)->pluck('id')->toArray();
                $students = Students::where(function($q){
                                auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);
                            })->where('students.active', true)
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->get(['students.*', 'student_classes.class_id as class_id']);
                // dd($students);
                $data['students'] = $students;
                return view('admin.student.bulk_list', $data);
                break;

            case 'DEPARTMENT':
                $data['title'] = __('text.students_for_department_of', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $programs = SchoolUnits::where(['school_units.unit_id'=>3])->where(['school_units.id'=>$request->item_id])
                        // ->join('school_units as faculties', ['faculties.parent_id'=>'school_units.id'])->where(['faculties.unit_id'=>2])
                        // ->join('school_units as departments', ['departments.parent_id'=>'school_units.id'])->where(['departments.unit_id'=>3])
                        ->join('school_units as programs', ['programs.parent_id'=>'school_units.id'])->where(['programs.unit_id'=>4])
                        ->pluck('programs.id')->toArray();
                $classes = ProgramLevel::whereIn('program_id', $programs)->pluck('id')->toArray();
                $students = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                            ->where('students.active', true)
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->get(['students.*', 'student_classes.class_id as class_id']);
                // dd($students);
                $data['students'] = $students;
                return view('admin.student.bulk_list', $data);
                break;

            case 'PROGRAM':
                $data['title'] = __('text.students_for', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $classes = ProgramLevel::where('program_id', $request->item_id)->pluck('id')->toArray();
                $students = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->where('students.active', true)
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->get(['students.*', 'student_classes.class_id as class_id']);
                // dd($students);
                $data['students'] = $students;
                return view('admin.student.bulk_list', $data);
                break;
                

            case 'CLASS':
                $data['title'] = __('text.all_students_for', ['unit'=>ProgramLevel::find($request->item_id)->name()]);
                $students = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                            ->where('students.active', true)
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->orderBy('students.name')->where('class_id', $request->item_id)->where('year_id', '=', $year)
                            ->distinct()->get(['students.*', 'student_classes.class_id as class_id']);
                $data['students'] = $students;
                return view('admin.student.bulk_list', $data);
                break;

            case 'LEVEL':
                $level = Level::find($request->item_id);
                $data['title'] = __('text.all_students_for', ['unit'=>$level->level??''.' - '.Batch::find($request->year_id)->name]);
                $classes = ProgramLevel::where('level_id', '=', $level->id)->pluck('id')->toArray();
                $students = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                            ->where('students.active', true)
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $request->year_id)
                            ->orderBy('students.name')->distinct()->get(['students.*', 'student_classes.class_id']);
                $data['students'] = $students;
                return view('admin.student.bulk_list', $data);
                break;
            
        }
    }

    public function bulk_message_notifications(Request $request)
    {
        $recipients = $request->recipients;
        $year = $request->year_id ?? Helpers::instance()->getCurrentAccademicYear();
        # code...
        switch($request->filter){
            case 'SCHOOL':
                $data['title'] = "Send Message Notification";
                $data['target'] = SchoolUnits::find($request->item_id)->name ?? null;
                return view('admin.student.bulk_messages', $data);
            
            case 'FACULTY' :
                $data['title'] = "Send Message Notification";
                $data['target'] = SchoolUnits::find($request->item_id)->name ?? null;
                return view('admin.student.bulk_messages', $data);

            case 'DEPARTMENT':
                $data['title'] = "Send Message Notification";
                $data['target'] = SchoolUnits::find($request->item_id)->name ?? null;
                return view('admin.student.bulk_messages', $data);

            case 'PROGRAM':
                $data['title'] = "Send Message Notification";
                $data['target'] = SchoolUnits::find($request->item_id)->name ?? null;
                return view('admin.student.bulk_messages', $data);
                

            case 'CLASS':
                $data['title'] = "Send Message Notification";
                $data['target'] = ProgramLevel::find($request->item_id)->name();
                return view('admin.student.bulk_messages', $data);

            case 'LEVEL':
                $level = Level::find($request->item_id);
                $data['title'] = "Send Message Notification";
                $data['target'] = $level->level??''.' - '.Batch::find($request->year_id)->name;
                return view('admin.student.bulk_messages', $data);
            
        }
    }
    public function bulk_message_notifications_save(Request $request)
    {
        $request->validate(['text'=>'required']);
        $recipients = $request->recipients;
        $recipients_field = $recipients == 'students' ? 'phone' : 'parent_phone_number';
        $year = $request->year_id ?? Helpers::instance()->getCurrentAccademicYear();
        # code...
        switch($request->filter){
            case 'SCHOOL':
                $data['title'] = __('text.students_for_school_of', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $programs = SchoolUnits::where(['school_units.unit_id'=>1])->where(['school_units.id'=>$request->item_id])
                        // ->join('school_units as faculties', ['faculties.parent_id'=>'school_units.id'])->where(['faculties.unit_id'=>2])
                        ->join('school_units as departments', ['departments.parent_id'=>'school_units.id'])->where(['departments.unit_id'=>3])
                        ->join('school_units as programs', ['programs.parent_id'=>'departments.id'])->where(['programs.unit_id'=>4])
                        ->pluck('programs.id')->toArray();
                $classes = ProgramLevel::whereIn('program_id', $programs)->pluck('id')->toArray();
                $contacts = Students::where(function($q){
                                auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);
                            })
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->pluck($recipients_field)->toArray();
                
                Self::sendSmsNotificaition($request->text, $contacts);
                
                // dd($students);
                // $data['students'] = $students;
                // return view('admin.student.bulk_list', $data);
                break;
            
            case 'FACULTY' :
                $data['title'] = __('text.students_for_faculty_of', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $programs = SchoolUnits::where(['school_units.unit_id'=>2])->where(['school_units.id'=>$request->item_id])
                        // ->join('school_units as faculties', ['faculties.parent_id'=>'school_units.id'])->where(['faculties.unit_id'=>2])
                        ->join('school_units as departments', ['departments.parent_id'=>'school_units.id'])->where(['departments.unit_id'=>3])
                        ->join('school_units as programs', ['programs.parent_id'=>'departments.id'])->where(['programs.unit_id'=>4])
                        ->pluck('programs.id')->toArray();
                $classes = ProgramLevel::whereIn('program_id', $programs)->pluck('id')->toArray();
                $contacts = Students::where(function($q){
                                auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);
                            })
                            ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->pluck($recipients_field)->toArray();


                Self::sendSmsNotificaition($request->text, $contacts);
                
                // dd($students);
                // $data['students'] = $students;
                // return view('admin.student.bulk_list', $data);
                break;

            case 'DEPARTMENT':
                $data['title'] = __('text.students_for_department_of', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $programs = SchoolUnits::where(['school_units.unit_id'=>3])->where(['school_units.id'=>$request->item_id])
                        // ->join('school_units as faculties', ['faculties.parent_id'=>'school_units.id'])->where(['faculties.unit_id'=>2])
                        // ->join('school_units as departments', ['departments.parent_id'=>'school_units.id'])->where(['departments.unit_id'=>3])
                        ->join('school_units as programs', ['programs.parent_id'=>'school_units.id'])->where(['programs.unit_id'=>4])
                        ->pluck('programs.id')->toArray();
                $classes = ProgramLevel::whereIn('program_id', $programs)->pluck('id')->toArray();
                $contacts = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                                        ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                        ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->pluck($recipients_field)->toArray();
                // dd($students);
                
                Self::sendSmsNotificaition($request->text, $contacts);
                
                // $data['students'] = $students;
                // return view('admin.student.bulk_list', $data);
                break;

            case 'PROGRAM':
                $data['title'] = __('text.students_for', ['unit'=>SchoolUnits::find($request->item_id)->name ?? null]);
                $classes = ProgramLevel::where('program_id', $request->item_id)->pluck('id')->toArray();
                $contacts = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                                        ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $year)->orderBy('students.name')->distinct()->pluck($recipients_field)->toArray();
                // dd($students);

                Self::sendSmsNotificaition($request->text, $contacts);

                // $data['students'] = $students;
                // return view('admin.student.bulk_list', $data);
                break;
                

            case 'CLASS':
                $data['title'] = __('text.all_students_for', ['unit'=>ProgramLevel::find($request->item_id)->name()]);
                $contacts = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                                        ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->orderBy('students.name')->where('class_id', $request->item_id)->where('year_id', '=', $year)
                            ->distinct()->pluck($recipients_field)->toArray();
                // $data['students'] = $students;
                
                Self::sendSmsNotificaition($request->text, $contacts);

                // return view('admin.student.bulk_list', $data);
                break;

            case 'LEVEL':
                $level = Level::find($request->item_id);
                $data['title'] = __('text.all_students_for', ['unit'=>$level->level??''.' - '.Batch::find($request->year_id)->name]);
                $classes = ProgramLevel::where('level_id', '=', $level->id)->pluck('id')->toArray();
                $contacts = Students::where(function($q){
                            auth()->user()->campus_id == null ? null : $q->where('campus_id', auth()->user()->campus_id);})
                                        ->join('student_classes', ['students.id'=>'student_classes.student_id'])
                            ->whereIn('class_id', $classes)->where('year_id', '=', $request->year_id)
                            ->orderBy('students.name')->distinct()->pluck($recipients_field)->toArray();
                // $data['students'] = $students;

                Self::sendSmsNotificaition($request->text, $contacts);

                // return view('admin.student.bulk_list', $data);
                break;
            
        }
    }
    
    public function set_program_grading_type(Request $request, $program_id)
    {
        # code...
        $data['title'] = __('text.set_program_grading_type_for', ['unit'=>SchoolUnits::find($program_id)->name]);
        return view('admin.grading.set_grading_type', $data);
    }

    public function save_program_grading_type(Request $request, $program_id)
    {
        # code...
        $valid = Validator::make($request->all(), ['grading_type'=>'required', 'program_id'=>'required']);
        if ($valid->fails()) {
            # code...
            return $valid->errors()->first();
        }

        $program  = SchoolUnits::find($program_id);
        $program->grading_type_id = $request->grading_type;
        $program->save();
        return back()->with('success', __('text.word_done'));
    }

    public function open_admission(Request $request)
    {
        # code...
        $data['title'] = "Configure Admission Session.";
        $data['sessions'] = Config::all();
        $data['current_session'] = Config::where('year_id', Helpers::instance()->getCurrentAccademicYear())->first();
        // return $data;
        return view('admin.setting.config_admission', $data);
    }

    public function set_open_admission(Request $request)
    {
        # code...
        $validity = Validator::make($request->all(), ['start_date'=>'required|date', 'end_date'=>'required|date']);
        if($validity->fails()){return back()->with('error', $validity->errors()->first());}

        $config = ['start_date'=>$request->start_date, 'end_date'=>$request->end_date];
        Config::updateOrInsert(['year_id'=>Helpers::instance()->getCurrentAccademicYear()], $config);
        return back()->with('success', __('text.word_done'));
    }

    public function applicants_report_by_degree(Request $request)
    {
        # code...
    }

    public function applicants_report_by_program(Request $request)
    {
        # code...
    }

    public function finance_report_general()
    {
        # code...
    }

    public function config_programs(Request $request, $cid = null)
    {
        # code...
        $data['title'] = "Configure Programs Per Entry Qualification";
        // return $data;

        
        $qlf = json_decode($this->api_service->certificates());
        if($qlf != null){
            $data['certs'] = $qlf->data;
            $data['cert'] = collect($qlf->data)->where('id', $cid)->first();
            if($data['cert'] != null){
                $data['cert_programs'] = collect(json_decode($this->api_service->certificatePrograms($cid))->data)->pluck('id')->toArray();
                $progs = json_decode($this->api_service->programs());
                // return $progs;
                if($progs != null){
                    $data['programs'] = $progs->data;
                }
            }
        }
        // return $data;
        return view('admin.setting.config_program', $data);
    }

    public function set_config_programs(Request $request, $entry_id)
    {
        # code...
        $validity = Validator::make($request->all(), ['programs'=>'required|array']);
        if($validity->fails()){return back()->with('error', $validity->errors()->first());}

        // save program configuration
        $programs = $request->programs;
        $response = $this->api_service->setCertificatePrograms($entry_id, $programs);
        return back()->with('message', $response);
    }

    public function config_degrees(Request $request, $campus_id = null)
    {
        # code...
        $data['title'] = "Configure Campus Degrees";
        $data['campuses'] = json_decode($this->api_service->campuses())->data;
        $data['degrees'] = json_decode($this->api_service->degrees())->data;
        if($campus_id != null){
            $degs = $this->api_service->campusDegrees($campus_id);
            if($degs != null){
                $data['campus_degrees'] = collect(json_decode($degs)->data)->pluck('id')->toArray();
            }
        }
           
        return view('admin.setting.configure_campus_degrees', $data);
    }

    public function set_config_degrees(Request $request, $cid)
    {
        # code...
        $validity = Validator::make($request->all(), ['campus_degrees'=>'array']);
        if($validity->fails()){return back()->with('error', $validity->errors()->first());}
        // return $request->all();
        if(($resp = json_decode($this->api_service->setCampusDegrees($cid, $request->campus_degrees??[]))->data) == '1'){
            return back()->with('success', 'Updated successfully');
        }else{
            return back()->with('error', $resp);
        };
    }


    public function applications()
    {
        # code...
        $data['title'] = "All Application Forms";
        $data['_this'] = $this;
        $data['applications'] = ApplicationForm::whereNotNull('transaction_id')->get();
        return view('admin.student.applications', $data);
    }

    // public function admit_student(Request $request, $id = null)
    // {
    //     # code...
    //     ApplicationForm::find($id)->update(['admitted', true]);
    //     return back()->with('success', __('text.word_done'));
    // }

    public function application_details(Request $request, $id)
    {
        # code...
        $data['application'] = ApplicationForm::find($id);
        $data['title'] = "Application Details For ".$data['application']->name;
        
    }



    
    public function print_application_form(Request $request, $id = null)
    {
        # code...
        if($id == null){
            $data['title'] = "Print Student Application Form";
            $data['_this'] = $this;
            $data['action'] = __('text.word_print');
            $data['applications'] = ApplicationForm::whereNotNull('transaction_id')->get();
            return view('admin.student.applications', $data);
        }

        $application = ApplicationForm::find($id);
        $data['campuses'] = json_decode($this->api_service->campuses())->data;
        $data['application'] = ApplicationForm::find($id);
        $data['degree'] = collect(json_decode($this->api_service->degrees())->data??[])->where('id', $data['application']->degree_id)->first();
        $data['campus'] = collect($data['campuses'])->where('id', $data['application']->campus_id)->first();
        $data['certs'] = json_decode($this->api_service->certificates())->data;
        
        $data['programs'] = json_decode($this->api_service->campusDegreeCertificatePrograms($data['application']->campus_id, $data['application']->degree_id, $data['application']->entry_qualification))->data;
        $data['cert'] = collect($data['certs'])->where('id', $data['application']->entry_qualification)->first();
        $data['program1'] = collect($data['programs'])->where('id', $data['application']->program_first_choice)->first();
        $data['program2'] = collect($data['programs'])->where('id', $data['application']->program_second_choice)->first();
        
        // $title = $application->degree??''.' APPLICATION FOR '.$application->campus->name??' --- '.' CAMPUS';
        $title = "APPLICATION FORM FOR ".$data['degree']->deg_name;
        $data['title'] = $title;

        if(in_array(null, array_values($data))){ return redirect(route('student.application.start', [0, $id]))->with('message', "Make sure your form is correctly filled and try again.");}
        // return view('student.online.form_dawnloadable', $data);
        $pdf = PDF::loadView('student.online.form_dawnloadable', $data);
        $filename = $title.' - '.$application->name.'.pdf';
        return $pdf->download($filename);
    }

    public function edit_application_form(Request $request, $id = null)
    {
        # code...
        if($id == null){
            $data['title'] = "Print Student Application Form";
            $data['_this'] = $this;
            $data['action'] = __('text.word_edit');
            $data['applications'] = ApplicationForm::whereNotNull('transaction_id')->where('year_id', Helpers::instance()->getCurrentAccademicYear())->get();
            return view('admin.student.applications', $data);
        }

        # code...
        // return $this->api_service->campuses();
        $data['campuses'] = json_decode($this->api_service->campuses())->data;
        $data['application'] = ApplicationForm::find($id);

        if($data['application']->degree_id != null){
            $data['degree'] = collect(json_decode($this->api_service->degrees())->data)->where('id', $data['application']->degree_id)->first();
        }
        if($data['application']->campus_id != null){
            $data['campus'] = collect($data['campuses'])->where('id', $data['application']->campus_id)->first();
        }
        if($data['application']->degree_id != null){
            $data['certs'] = json_decode($this->api_service->certificates())->data;
        }
        if($data['application']->entry_qualification != null){
            $data['programs'] = json_decode($this->api_service->campusDegreeCertificatePrograms($data['application']->campus_id, $data['application']->degree_id, $data['application']->entry_qualification))->data;
            $data['cert'] = collect($data['certs'])->where('id', $data['application']->entry_qualification)->first();
        }
        if($data['application']->program_first_choice != null){
            $data['program1'] = collect($data['programs'])->where('id', $data['application']->program_first_choice)->first();
            $data['program2'] = collect($data['programs'])->where('id', $data['application']->program_second_choice)->first();
            // return $data;
        }
        
        $data['title'] = "APPLICATION FORM FOR ".$data['degree']->deg_name;
        return view('admin.student.edit_form', $data);
        
    }
    public function update_application_form(Request $request, $id)
    {
        # code...
        $validity = Validator::make($request->all(), ['name'=>'required']);
        if($validity->fails()){
            return back()->with('error', $validity->errors()->first());
        }

        $data = ['name'=>$request->name];
        ApplicationForm::find($id)->update($data);
        return back()->with('success', __('text.word_done'));
    }

    public function uncompleted_application_form(Request $request, $id=null)
    {
        # code...
        if($id == null){
            $data['title'] = "Uncompleted Application Forms";
            $data['_this'] = $this;
            $data['action'] = __('text.word_show');
            $data['applications'] = ApplicationForm::whereNull('transaction_id')->where('year_id', Helpers::instance()->getCurrentAccademicYear())->get();
            return view('admin.student.applications', $data);
        }

        // return $this->api_service->campuses();
        $data['campuses'] = json_decode($this->api_service->campuses())->data;
        $data['application'] = ApplicationForm::find($id);

        if($data['application']->degree_id != null){
            $data['degree'] = collect(json_decode($this->api_service->degrees())->data)->where('id', $data['application']->degree_id)->first();
        }
        if($data['application']->campus_id != null){
            $data['campus'] = collect($data['campuses'])->where('id', $data['application']->campus_id)->first();
        }
        if($data['application']->degree_id != null){
            $data['certs'] = json_decode($this->api_service->certificates())->data;
        }
        if($data['application']->entry_qualification != null){
            $data['programs'] = json_decode($this->api_service->campusDegreeCertificatePrograms($data['application']->campus_id, $data['application']->degree_id, $data['application']->entry_qualification))->data;
            $data['cert'] = collect($data['certs'])->where('id', $data['application']->entry_qualification)->first();
        }
        if($data['application']->program_first_choice != null){
            $data['program1'] = collect($data['programs'])->where('id', $data['application']->program_first_choice)->first();
            $data['program2'] = collect($data['programs'])->where('id', $data['application']->program_second_choice)->first();
            // return $data;
        }
        
        $data['title'] = "APPLICATION FORM FOR ".$data['degree']->deg_name;
        return view('admin.student.show_form', $data);
    }

    public function distant_application_form(Request $request, $id)
    {
        # code...
    }

    public function admission_letter(Request $request, $id = null)
    {
        # code...
        if($id == null){
            $data['title'] = "Uncompleted Application Forms";
            $data['_this'] = $this;
            $data['action'] = __('text.word_print');
            $data['applications'] = ApplicationForm::whereNotNull('transaction_id')->where('admitted', true)->where('year_id', Helpers::instance()->getCurrentAccademicYear())->get();
            return view('admin.student.applications', $data);
        }
        // print admission letter
        $appl = ApplicationForm::find($id);
        $data['title'] = "ADMISSION LETTER";
        $data['name'] = $appl->name;
        $data['matric'] = $appl->matric;
        $data['director_name'] = "Mr. Behonh Tolly J. Olivier";
        $data['dean_name'] = "Dr. Derick N. Awambeng";
        $data['fee_dateline'] = "3rd October 2022.";
        $data['help_email'] = "admission@slui.org";
        
        $data['campus'] = collect(json_decode($this->api_service->campuses())->data)->where('id', $appl->campus_id)->first();
        $data['program'] = collect(json_decode($this->api_service->programs())->data)->where('id', $appl->program_first_choice)->first();
        // return view('admin.student.admission_letter', $data);
        $pdf = Pdf::loadView('admin.student.admission_letter', $data);

        return $pdf->download("Admission_Letter_{$appl->matric}.pdf");
    }

    public function admit_application_form(Request $request, $id=null)
    {
        # code...
        if($id == null){
            $data['title'] = "Admit Student";
            $data['_this'] = $this;
            $data['action'] = __('text.word_admit');
            $data['applications'] = ApplicationForm::whereNotNull('transaction_id')->where('admitted', 0)->where('year_id', Helpers::instance()->getCurrentAccademicYear())->get();
            return view('admin.student.applications', $data);
        }
        if(!$request->has('matric') or ($request->matric == null)){
            // 
            // GENERATE MATRICULE
            $application = ApplicationForm::find($id);
            if(($programs = json_decode($this->api_service->programs())->data) != null){
                $program = collect($programs)->where('id', $application->program_first_choice)->first()??null;
                if($program != null){
                    dd($program);
                    $year = substr(Batch::find(Helpers::instance()->getCurrentAccademicYear())->name, 2, 2);
                    $prefix = $program->prefix;//3 char length
                    $max_count = '';
                    if($prefix == null){
                        return back()->with('error', 'Matricule generation prefix not set.');
                    }
                    $max_matric = json_decode($this->api_service->max_matric($prefix, $year))->data; //matrics starting with '$prefix' sort
                    // dd($max_matric);
                    if($max_matric == null){
                        $max_count = 0;
                    }else{
                        $max_count = intval(substr($max_matric, strlen($prefix)+4));
                    }
                    $next_count = substr('0000'.($max_count+1), -4);
                    $student_matric = $prefix.'/'.$year.'/'.$next_count;

                    if(ApplicationForm::where('matric', $student_matric)->where('id', '!=', $id)->count() == 0){
                        $data['title'] = "Student Admission";
                        $data['application'] = $application;
                        $data['program'] = $program;
                        $data['matricule'] = $student_matric;
                        $data['campus'] = collect(json_decode($this->api_service->campuses())->data)->where('id', $application->campus_id)->first();
                        return view('admin.student.confirm_admission', $data);
                    }
                    return back()->with('error', 'Failed to generate matricule');
                }
            }
        }
    }


    public function admit_student(Request $request, $id)
    {
        # code...
        $validity = Validator::make($request->all(), ['matric'=>'required']);
        if($validity->fails()){return back()->with('error', 'Missing matricule');}
        $application = ApplicationForm::find($id);
        // dd($application->toJson());
        // (new ApplicationForm())-
        
        
        // POST STUDENT TO SCHOOL SYSTEM
        $application->matric = $request->matric;
        $resp = json_decode($this->api_service->store_student($application->toArray()))->data??null;
        // dd($resp);
        if($resp != null){
            if($resp->status ==1){
                $application->update(['matric'=>$request->matric, 'admitted'=>1]);

                // Send sms/email notification
                return redirect(route('admin.applications.admit'))->with('success', "Student admitted successfully.");
            }else
            return back()->with('error', $resp);
        }



    }


    public function application_form_change_program(Request $request, $id = null)
    {
        # code...
        if($id == null){
            $data['title'] = "Change Student Program";
            $data['_this'] = $this;
            $data['action'] = __('text.change_program');
            $data['applications'] = ApplicationForm::where('admitted', true)->where('year_id', Helpers::instance()->getCurrentAccademicYear())->get();
            return view('admin.student.applications', $data);
        }

        // return $this->api_service->campuses();
        $data['campuses'] = json_decode($this->api_service->campuses())->data;
        $data['application'] = ApplicationForm::find($id);

        if($data['application']->degree_id != null){
            $data['degree'] = collect(json_decode($this->api_service->degrees())->data)->where('id', $data['application']->degree_id)->first();
        }
        if($data['application']->campus_id != null){
            $data['campus'] = collect($data['campuses'])->where('id', $data['application']->campus_id)->first();
        }
        if($data['application']->degree_id != null){
            $data['certs'] = json_decode($this->api_service->certificates())->data;
        }
        if($data['application']->entry_qualification != null){
            $data['programs'] = json_decode($this->api_service->campusDegreeCertificatePrograms($data['application']->campus_id, $data['application']->degree_id, $data['application']->entry_qualification))->data;
            $data['cert'] = collect($data['certs'])->where('id', $data['application']->entry_qualification)->first();
        }
        if($data['application']->program_first_choice != null){
            $data['program1'] = collect($data['programs'])->where('id', $data['application']->program_first_choice)->first();
            $data['program2'] = collect($data['programs'])->where('id', $data['application']->program_second_choice)->first();
            // return $data;
        }
        if($data['application']->level != null){
            $data['levels'] = json_decode($this->api_service->levels())->data;
        }
        
        $data['title'] = "CHANGE PROGRAM FOR ".$data['degree']->deg_name;
        return view('admin.student.change_program', $data);
    }

    public function change_program(Request $request, $id)
    {
        # code...
        $validity = Validator::make($request->all(), ['current_program'=>'required', 'new_program'=>'required', 'level'=>'required']);
        if($validity->fails()){
            return back()->with('error', $validity->errors()->first());
        }
        $data = ['program_first_choice'=>$request->new_program, 'level'=>$request->level];
        ApplicationForm::find($id)->update($data);

        // UPDATE STUDENT IN SCHOOL SYSTEM.
        // 
        // GENERATE MATRICULE
        $application = ApplicationForm::find($id);
        if(($programs = json_decode($this->api_service->programs())->data) != null){
            $program = collect($programs)->where('id', $application->program_first_choice)->first()??null;
            if($program != null){

                $year = substr(Batch::find(Helpers::instance()->getCurrentAccademicYear())->name, 2, 2);
                $prefix = $program->prefix;//3 char length
                $max_count = '';
                if($prefix == null){
                    return back()->with('error', 'Matricule generation prefix not set.');
                }
                $max_matric = json_decode($this->api_service->max_matric($prefix, $year))->data; //matrics starting with '$prefix' sort
                if($max_matric == null){
                    $max_count = 0;
                }else{
                    $max_count = intval(substr($max_matric, strlen($prefix)+4));
                }
                $next_count = substr('0000'.($max_count+1), -4);
                $student_matric = $prefix.'/'.$year.'/'.$next_count;

                if(ApplicationForm::where('matric', $student_matric)->count() == 0){
                    $data['title'] = "Change Student Program";
                    $data['application'] = $application;
                    $data['program'] = $program;
                    $data['matricule'] = $student_matric;
                    $data['campus'] = collect(json_decode($this->api_service->campuses())->data)->where('id', $application->campus_id)->first();
                    return view('admin.student.confirm_change_program', $data);
                }
                return back()->with('error', 'Failed to generate matricule');
            }
        }
        return back()->with('success', 'Done');
    }

    public function change_program_save(Request $request, $id)
    {
        # code...
        $validity = Validator::make($request->all(), ['matric'=>'required']);
        if($validity->fails()){return back()->with('error', 'Missing matricule');}
        $application = ApplicationForm::find($id);
        // dd($application->toJson());
        // (new ApplicationForm())-
        
        
        // POST STUDENT TO SCHOOL SYSTEM
        $resp = json_decode($this->api_service->update_student($application->matric, ['program'=>$application->program_first_choice, 'level'=>$application->level, 'matric'=>$request->matric]))->data??null;
        // dd($resp);
        if($resp != null){
            if($resp->status ==1){
                // $application->matric = $request->matric;
                $application->update(['matric'=>$request->matric, 'admitted'=>1]);

                // Send sms/email notification
                return redirect(route('admin.applications.admit'))->with('success', "Program changed successfully.");
            }else
            return back()->with('error', $resp);
        }



    }

    
    public function start_application (Request $request, $step, $application_id = null)
    {
        try {

            if(auth('student')->user()->applicationForms()->whereNotNull('transaction_id')->where('year_id', Helpers::instance()->getCurrentAccademicYear())->count() > 0){
                return back()->with('error', "You are allowed to submit only one application form per year");
            }

            // check if application is open now
            if(!(Helpers::instance()->application_open())){
                return redirect(route('student.home'))->with('error', 'Application closed for '.Batch::find(Config::all()->last()->year_id)->name);
            }
            # code...
            $data['step'] = $step;
            // return $this->api_service->campuses();
            $data['campuses'] = json_decode($this->api_service->campuses())->data;
            $application = ApplicationForm::where(['student_id'=>auth('student')->id(), 'year_id'=>Helpers::instance()->getCurrentAccademicYear()])->first();
            if($application == null){
                $application = new ApplicationForm();
                $application->student_id = auth('student')->id();
                $application->year_id = Helpers::instance()->getCurrentAccademicYear();
                $application->save();
            }
            $data['application'] = $application;
    
            if($data['application']->degree_id != null){
                $data['degree'] = collect(json_decode($this->api_service->degrees())->data)->where('id', $data['application']->degree_id)->first();
            }
            if($data['application']->campus_id != null){
                $data['campus'] = collect($data['campuses'])->where('id', $data['application']->campus_id)->first();
            }
            if($data['application']->degree_id != null){
                $data['certs'] = json_decode($this->api_service->certificates())->data;
            }
            if($data['application']->entry_qualification != null){
                $data['programs'] = json_decode($this->api_service->campusDegreeCertificatePrograms($data['application']->campus_id, $data['application']->degree_id, $data['application']->entry_qualification))->data;
                $data['cert'] = collect($data['certs'])->where('id', $data['application']->entry_qualification)->first();
            }
            if($data['application']->program_first_choice != null){
                $data['program1'] = collect($data['programs'])->where('id', $data['application']->program_first_choice)->first();
                $data['program2'] = collect($data['programs'])->where('id', $data['application']->program_second_choice)->first();
                // return $data;
            }
            
            $data['title'] = (isset($data['degree']) and ($data['degree'] != null)) ? $data['degree']->deg_name." APPLICATION FOR DOUALA-BONABERI" : "APPLICATION FOR DOUALA-BONABERI";
            return view('student.online.fill_form', $data);
        } catch (\Throwable $th) {
            //throw $th;
            return back()->with('error', $th->getMessage());
        }
    }

    public function persist_application(Request $request, $step, $application_id)
    {
        # code...
        // return $request->all();
        
        // check if application is open now
        if(!(Helpers::instance()->application_open())){
            return redirect(route('student.home'))->with('error', 'Application closed for '.Batch::find(Config::all()->last()->year_id)->name);
        }
        switch ($step) {
            case 1:
                # code...
                $validity = Validator::make($request->all(), [
                    'campus_id'=>'required', 'degree_id'=>'required'
                ]);
                break;
            
            case 2:
                # code...
                // return $request->all();
                $validity = Validator::make($request->all(), [
                    "name"=>'required',"gender"=> "required","dob"=> "required", "pob"=> "required", "nationality"=> "required",
                    "region"=> "required", "division"=> "required", "residence"=> "required", "phone"=> "required", "email"=> "required|email",
                    "referer"=> "required", "high_school"=> "required", "campus_id"=> "required", "entry_qualification"=> "required"
                ]);
                break;
            
            case 3:
                # code...
                $validity = Validator::make($request->all(), [
                    'program_first_choice'=>'required', 'program_second_choice'=>'required',
                ]);
                break;
            
            case 4:
                # code...
                
                $validity = Validator::make($request->all(), [
                    // 'first_spoken_language'=>'required', 'first_written_language'=>'required',
                    'employments'=>'array', 'previous_training'=>'array'
                ]);
                break;
                
            case 5:
                # code...
                // return $request->all();
                // $validity = Validator::make($request->all(), [
                //     'has_health_problem'=>'required|', 'has_health_allergy'=>'required', 'has_disability'=>'required',
                //     'health_problem'=>'required_if:has_health_problem,yes', 'health_allergy'=>'required_if:has_health_allergy,yes', 
                //     'disability'=>'required_if:has_disability,yes',
                // ]);
                $validity = Validator::make($request->all(), [
                    'fee_payer'=>'required', 'fee_payer_name'=>'required', 'fee_payer_residence'=>'required',
                    'fee_payer_tel'=>'required', 'fee_payer_occupation'=>'required'
                ]);
                break;
                
            case 6:
                $validity = Validator::make($request->all(), [
                    
                ]);
                # code...
                break;
            
            case 7:
                # code...
                // return $request->all();
                // momo-number validated with country code for cameroon: 237
                $validity = Validator::make($request->all(), [
                    "momo_number"=> "required|size:9", "amount"=> "required|numeric|min:1",
                    // "momo_screenshot"=> "file"
                ]);
                break;
            
        }

        if($validity->fails()){
            return back()->with('error', $validity->errors()->first());
        }
        // return $request->all();

        // persist data
        $data = [];
        if($step == 4){
            $data_p1=[];
            $_data = $request->previous_training;
            // return $_data;
            if($_data != null){
                foreach ($_data['school'] as $key => $value) {
                    $data_p1[] = ['school'=>$value, 'year'=>$_data['year'][$key], 'course'=>$_data['course'][$key], 'certificate'=>$_data['certificate'][$key]];
                }
                $data['previous_training'] = json_encode($data_p1);
                // return $data;
            }
            $data_p2 = [];
            $e_data = $request->employments;
            if($e_data != null){
                foreach ($e_data['employer'] as $key => $value) {
                    $data_p2[] = ['employer'=>$value, 'post'=>$e_data['post'][$key], 'start'=>$e_data['start'][$key], 'end'=>$e_data['end'][$key], 'type'=>$e_data['type'][$key]];
                }
                $data['employments'] = json_encode($data_p2);
                // return $data;
            }
            $data = collect($data)->filter(function($value, $key){return $key != '_token';})->toArray();
            $application = ApplicationForm::updateOrInsert(['id'=> $application_id, 'student_id'=>auth('student')->id()], $data);
        }
        elseif($step ==7){
            
            // MAKE API CALL TO PERFORM PAYMENT OF APPLICATION FEE
            // check if token exist and hasn't expired or get new token otherwise
            if(cache('tranzak_api_token') == null or Carbon::parse(cache('tranzak_api_token_expire'))->isAfter(now())){
                // get and cache different token
                $response = Http::post(config('tranzak.base').config('tranzak.token'), ['appId'=>config('tranzak.app_id'), 'appKey'=>config('tranzak.api_key')]);
                if($response->status() == 200){
                    // return json_decode($response->body())->data;
                    // return Carbon::createFromTimestamp(time() + json_decode($response->body())->data->expiresIn);
                    // cache token and token expirationtot session
                    cache(['tranzak_api_token'=> json_decode($response->body())->data->token]);
                    cache(['tranzak_api_token_expire'=>Carbon::createFromTimestamp(time() + json_decode($response->body())->data->expiresIn)]);
                }
            }
            // Assumed there is a valid api token
            // Moving the performing the payment request proper
            $headers = ['Authorization'=>'Bearer '.cache('tranzak_api_token')];
            $request_data = ['mobileWalletNumber'=>'237'.$request->momo_number, 'mchTransactionRef'=>'_apl_fee_'.time().'_'.random_int(1, 9999), "amount"=> $request->amount, "currencyCode"=> "XAF", "description"=>"Payment for application fee into ST LOUIS UNIVERSITY INSTITUTE"];
            $_response = Http::withHeaders($headers)->post(config('tranzak.base').config('tranzak.direct_payment_request'), $request_data);
            if($_response->status() == 200){
                // return json_decode($_response->body())->data;
                // save transaction and track it status

                session()->put('processing_tranzak_transaction_details', json_encode(json_decode($_response->body())->data));
                // return $this->pending_payment(array_push((), ['application_id']));
                return redirect()->to(route('student.application.payment.processing', $application_id))->with(['data'=>json_decode($_response->body())->data]);
            }

            // END OF PAYMENT PROCESS
            // $data = collect($data)->filter(function($value, $key){return $key != '_token';})->toArray();
            // $application = ApplicationForm::updateOrInsert(['id'=> $application_id, 'student_id'=>auth('student')->id()], $data);
        }else{
            $data = $request->all();
            $data = collect($data)->filter(function($value, $key){return $key != '_token';})->toArray();
            $application = ApplicationForm::updateOrInsert(['id'=> $application_id, 'student_id'=>auth('student')->id()], $data);
        }

        // $application->update($data);
        if($step == 7){
            // Form fully filled
            return redirect(route('student.home'))->with('success', 'Application form completely filled.');
        }
        // $application->update($data);
        if($step == 3){
            // Form fully filled
            $appl = ApplicationForm::find($application_id);
            // return 'xyz';
                $degs = json_decode($this->api_service->campusDegrees($appl->campus_id))->data;
                if (($degree = collect($degs)->where('id', $appl->degree_id)->first()) != null) {
                    if($degree->deg_name != 'MASTER DEGREE PROGRAMS'){
                        return redirect(route('student.application.start', [$step+1, $application_id]));
                    }
                }
        }
        $step = $request->step;
        return redirect(route('student.application.start', [$step, $application_id]));
    }

    public function pending_payment(Request $request, $application_id)
    {
        # code...
        
        // check if application is open now
        if(!(Helpers::instance()->application_open())){
            return redirect(route('student.home'))->with('error', 'Application closed for '.Helpers::instance()->getYear()->name);
        }
        $data['title'] = "Processing Transaction";
        $data['form_id'] = $application_id;
        $data['transaction'] = json_decode(session()->get('processing_tranzak_transaction_details'));
        // return $data;
        return view('student.online.processing_payment', $data);
        
    }

    public function pending_complete(Request $request, $appl_id)
    {
        # code...
        try {
            
            // check if application is open now
            if(!(Helpers::instance()->application_open())){
                return redirect(route('student.home'))->with('error', 'Application closed for '.Helpers::instance()->getYear()->name);
            }
            //code...
            $transaction_status = json_decode($request->qstring);
            // return $transaction_status;
            switch ($transaction_status->status) {
                case 'SUCCESSFUL':
                    # code...
                    // save transaction and update application_form
                    $transaction = ['request_id'=>$transaction_status->requestId, 'amount'=>$transaction_status->amount, 'currency_code'=>$transaction_status->currencyCode, 'purpose'=>"application fee", 'mobile_wallet_number'=>$transaction_status->mobileWalletNumber, 'transaction_ref'=>$transaction_status->mchTransactionRef, 'app_id'=>$transaction_status->appId, 'transaction_id'=>$transaction_status->transactionId, 'transaction_time'=>$transaction_status->transactionTime, 'payment_method'=>$transaction_status->payer->paymentMethod, 'payer_user_id'=>$transaction_status->payer->userId, 'payer_name'=>$transaction_status->payer->name, 'payer_account_id'=>$transaction_status->payer->accountId, 'merchant_fee'=>$transaction_status->merchant->fee, 'merchant_account_id'=>$transaction_status->merchant->accountId, 'net_amount_recieved'=>$transaction_status->merchant->netAmountReceived];
                    $transaction_instance = new Transaction($transaction);
                    $transaction_instance->save();
    
                    $appl = ApplicationForm::find($appl_id);
                    $appl->transaction_id = $transaction_instance->id;
                    $appl->save();
    
                    // SEND SMS
                    $phone_number = auth('student')->user()->phone;
                    if(str_starts_with($phone_number, '+')){
                        $phone_number = substr($phone_number, '4');
                    }
                    if(strlen($phone_number) > 9){
                        $phone_number = substr($phone_number, '3');
                    }
                    $tempArray = ['237699131895'];
                    
                    $formatContactArray = SMSHelpers::formatPhoneNumbers($tempArray);
                    //send sms to formatted array of numbers
                    $message="Application form for ST. LOUIS UNIVERSITY INSTITUTE submitted successfully.";
                    $this->sendSMS('237699131895', $message);
    
                    return redirect(route('student.application.form.download'))->with('success', "Payment successful.");
                    break;
                
                case 'CANCELLED':
                    # code...
                    // notify user
                    return redirect(route('student.home'))->with('message', 'Payment Not Made. The request was cancelled.');
                    break;
                
                case 'FAILED':
                    # code...
                    return redirect(route('student.home'))->with('error', 'Payment failed.');
                    break;
                
                case 'REVERSED':
                    # code...
                    return redirect(route('student.home'))->with('message', 'Payment failed. The request was reversed.');
                    break;
                
                default:
                    # code...
                    break;
            }
            return redirect(route('student.home'))->with('error', 'Payment failed. Unrecognised transaction status.');
        } catch (\Throwable $th) {
            //throw $th;
            return back()->with('error', $th->getMessage());
        }
    }

    public function submit_application(Request $request){
        
        // check if application is open now
        if(!(Helpers::instance()->application_open())){
            return redirect(route('student.home'))->with('error', 'Application closed for '.Helpers::instance()->getYear()->name);
        }
        $applications = auth('student')->user()->currentApplicationForms()->whereNull('transaction_id')->get();
        $data['title'] = "Submit Application";
        $data['applications'] = $applications;
        return view('student.online.submit_form', $data);
    }

    public function submit_application_save(Request $request, $appl_id)
    {
        # code...
        $application = ApplicationForm::find($appl_id);
        if($application != null){
            $application->submitted = 1;
            $application->save();
            return back()->with('success', 'Application submitted.');
        }
        return back()->with('error', 'Application could not be found.');
    }

}
