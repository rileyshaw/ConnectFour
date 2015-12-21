<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class APIController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create_room(){
        //need to validate the user still
        $roomname =  Input::get('room_name');
        $games = DB::select('select * from games where name = ?',$roomname);
        if( empty( $games ) )
        {
            DB::insert('insert into games (name, player1,player2,board,nextturn) values (?, ?,?,?,?)', [$roomname, null,null,null,1]);
            return 'Successfully added room';
        }else{
            return 'Error, room already exists';
        }
    }
    public function join_room(){


    }
}
