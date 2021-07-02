<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\PayIncomeResource;
use App\Models\Batch;
use App\Models\Income;
use App\Models\PayIncome;
use App\Models\SchoolUnits;
use App\Models\Students;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PayIncomeController extends Controller
{

    private $select = [
        'students.id',
        'pay_incomes.id as pay_income_id',
        'students.name as student_name',
        'incomes.name as income_name',
        'incomes.amount',
    ];



    /**
     * list all paid incomes
     */
    public function index()
    {
        $batch_id = Batch::find(\App\Helpers\Helpers::instance()->getCurrentAccademicYear())->id;
        $data['pay_incomes'] = DB::table('pay_incomes')
            ->join('incomes', 'incomes.id', '=', 'pay_incomes.income_id')
            ->join('students', 'students.id', '=', 'pay_incomes.student_id')
            ->join('school_units', 'school_units.id', '=', 'pay_incomes.class_id')
            ->where('pay_incomes.batch_id', $batch_id)
            ->select($this->select)
            ->paginate(5);
        $data['title'] = 'Pay Incomes';
        $data['years'] = Batch::all();
        $data['school_units'] = SchoolUnits::where('parent_id', '=', 0)->get();
        //  dd($data['school_units']);
        return view('admin.payIncome.index')->with($data);
    }




    /**
     * get pay_income for class for a year
     */
    public  function getPayIncomePerClassYear(Request $request)
    {
        $validate_data = $request->validate([
            'class_id' => 'required|numeric',
            'batch_id' => 'required|numeric',
            'section_id' => 'required|numeric',
            'circle' => 'required|numeric'
        ]);
        $data['pay_incomes'] = DB::table('pay_incomes')
            ->join('incomes', 'incomes.id', '=', 'pay_incomes.income_id')
            ->join('students', 'students.id', '=', 'pay_incomes.student_id')
            ->join('school_units', 'school_units.id', '=', 'pay_incomes.class_id')
            ->where('pay_incomes.batch_id', $request->batch_id)
            ->where('school_units.id', $request->class_id)
            ->select($this->select)
            ->paginate(5);
        $class_name = $this->getSchoolUnit($request->class_id);
        $data['title'] = 'Pay Incomes: ' . $class_name;
        $data['years'] = Batch::all();
        $data['school_units'] = SchoolUnits::where('parent_id', '=', 0)->get();
        return view('admin.payIncome.index')->with($data);
    }


    /**
     * show view form to find a student to collect income
     */
    public function create()
    {

        $data['title'] = 'Collect Income';
        return view('admin.payIncome.create')->with($data);
    }




    /**
     * get student by name or matricule
     */
    public function searchStudent($name)
    {

        $students = DB::table('student_classes')
            ->join('students', 'students.id', '=', 'student_classes.student_id')
            ->join('school_units', 'school_units.id', '=', 'student_classes.class_id')
            ->where('students.name', 'like', '%' . $name . '%')
            ->orWhere('students.matric', 'like', $name . '%')
            ->select('students.id',  'students.name', 'students.matric','students.gender', 'school_units.name as class_name', 'school_units.id as class_id')->get();

        return response()->json(['data' => PayIncomeResource::collection($students)]);
    }




    /**
     * show form to collect income for a student
     * @param int $class_id
     * @param int $student_id
     */
    public function collect($class_id, $student_id)
    {
        $student = Students::where('id', $student_id)->first();
        $data['title'] = 'Collect Income for ' . $student->name;
        $data['class_id'] = $class_id;
        $data['incomes'] = Income::all();
        $data['years'] = Batch::all();
        $data['student_id'] = $student_id;
        return view('admin.payIncome.collect')->with($data);
    }




    /**
     * store paid income
     * @param int $class_id
     * @param int $student_id
     */
    public function store(Request $request, $class_id, $student_id)
    {

        $validate_data = $request->validate([
            'income_id' => 'required|numeric',
            'batch_id' => 'required|numeric'
        ]);
        $created = PayIncome::create([
            'income_id' => $validate_data['income_id'],
            'batch_id' => $validate_data['batch_id'],
            'class_id' => $class_id,
            'student_id' => $student_id
        ]);
        return redirect()->route('admin.pay_income.index')->with('success', 'Payed Income successfully');
    }





    /**
     * get all sections of parent
     * @param int $id
     */
    public function getSections($id)
    {
        $sections = SchoolUnits::where('parent_id', $id)->get()->toArray();
        return response()->json(['data' => $sections]);
    }




    /**
     * get all classes of a section
     * 
     * @param int $id
     */
    public function getClasses($id)
    {
        $data = trim($id);
        $classes = SchoolUnits::where('parent_id', $data)->get()->toArray();
        return response()->json(['data' => $classes]);
    }

    /**
     * get schoolunit name
     * @param int id
     */
    private function getSchoolUnit($id)
    {
        $school_unit = SchoolUnits::where('id', $id)->pluck('name')[0];
        return $school_unit;
    }
}
