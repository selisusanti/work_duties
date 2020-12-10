<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Task;
use App\Constants\Constant;
use Illuminate\Support\Facades\Auth;
use Validator;
use DB;

class TaskController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {            

        $status             = $request->query('status');


        if(!empty($status)){
            $data =  Task::where("task.id_status",$status)
                    ->paginate($request->query('limit') ?? Constant::LIMIT_PAGINATION);
        }else{

            $data = Task::paginate($request->query('limit') ?? Constant::LIMIT_PAGINATION);
        }

        return response()->json([
            'success' => true,
            'status' => 200,
            'data'=> $data
        ]);
    }


    public function create(Request $request)
    {   
        $validator = Validator::make($request->all(), [
            'judul_task'      => 'string|required',
            'detail_task'     => 'string',
            'id_status'       => 'required|integer|exists:status,id',
            'id_user_send'      => 'required|integer|exists:users,id',
            'id_user_assign'      => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'status' => 401,
                'data'=> $validator->errors()
            ]);
         
        }

        DB::beginTransaction();
        try{
       
            $store = Task::create([
                'judul_task'      => $request->judul_task,
                'detail_task'     => $request->detail_task,
                'id_status'       => '1',
                'id_user_send'    => $request->id_user_send,
                'id_user_assign'  => $request->id_user_assign
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'status' => 200,
                'data'=> $store
            ]);
        
          
        } catch(\Error $e){

            DB::rollBack();
            return response()->json([
                'success' => false,
                'status' => 401,
                'data'=> "Gagal Simpan Data Task"
            ]);

        }     

    }


    public function updatestatus(Request $request, $id)
    {   
        $validator = Validator::make($request->all(), [
            'id_status'       => 'required|integer|exists:status,id',
        ]);

        DB::beginTransaction();
        try{
       
            $store              = Task::where('id',$id)->first();
            if(empty($store)){
                return response()->json([
                    'success' => false,
                    'status' => 401,
                    'data'=> "task tidak ada"
                ]);
             
            }
            if($store->id_user_send != Auth::user()->id){
                return response()->json([
                    'success' => false,
                    'status' => 401,
                    'data'=> "maaf anda tidak ada akses untuk update status task"
                ]);
            }
            
            $update        = $store->update([
                'id_status'     => $request->id_status
            ]); 
            
            DB::commit();
            return response()->json([
                'success' => true,
                'status' => 200,
                'data'=> $store
            ]);
        
          
        } catch(\Error $e){

            DB::rollBack();
            return response()->json([
                'success' => false,
                'status' => 401,
                'data'=> "Gagal Update Status Task"
            ]);

        }    

    }

    public function update(Request $request, $id)
    {   
        $validator = Validator::make($request->all(), [
            'judul_task'      => 'string|required',
            'detail_task'     => 'string',
            'id_status'       => 'required|integer|exists:status,id',
            'id_user_send'      => 'required|integer|exists:users,id',
            'id_user_assign'      => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'status' => 401,
                'data'=> $validator->errors()
            ]);
         
        }

        DB::beginTransaction();
        try{
       
            $store              = Task::where('id',$id)->first();
            if(empty($store)){
                return response()->json([
                    'success' => false,
                    'status' => 401,
                    'data'=> "task tidak ada"
                ]);
             
            }

            $update_user        = $store->update([
                'judul_task'      => $request->judul_task,
                'detail_task'     => $request->detail_task,
                'id_status'       => '1',
                'id_user_send'    => $request->id_user_send,
                'id_user_assign'  => $request->id_user_assign
            ]); 
            
            DB::commit();
            return response()->json([
                'success' => true,
                'status' => 200,
                'data'=> $store
            ]);
        
          
        } catch(\Error $e){

            DB::rollBack();
            return response()->json([
                'success' => false,
                'status' => 401,
                'data'=> "Gagal Simpan Data Task"
            ]);

        }     

    }

    public function delete($id)
    {  

        $employee = Task::where('id',$id)->first();

        if(empty($employee)){

            return response()->json([
                'success' => false,
                'status' => 401,
                'data'=> "Data Tidak di temukan"
            ]); 

        }

        if($employee->status == Constant::TASK_AKTIF){

            return response()->json([
                'success' => false,
                'status' => 401,
                'data'=> "Task Sedang Dikerjakan"
            ]); 

        }
        
    
        Task::where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'status' => 200,
            'data'=> "Hapus Data Sukses"
        ]); 
            
        
    }

}
