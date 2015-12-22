<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Input;
use DB;

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
        $games = DB::select('select * from games where name = ?',[$roomname]);
        if( empty( $games ) )
        {
            DB::insert('insert into games (name, player1,player2,board,nextturn) values (?,?,?,?,?)', [$roomname, null,null,null,1]);
            return 'Successfully added room';
        }else{
            return 'Error, room already exists';
        }
    }
    public function join_room(){
        $playername = Input::get('name');
        $roomname =  Input::get('room_name');
        $game_to_join = DB::select('select * from games where name = ?',[$roomname]);
        if( empty( $game_to_join ) ) {
            return 'Error: Room does not exist';
        }
        if( empty( $playername ) ) {
            return 'Error: Send a valid username';
        }
        if(empty($game_to_join[0]->player1)){
            DB::table('games')->where('name', $roomname)->update(['player1' => $playername]);
            return 'Waiting for other player to join';
        }else if(empty($game_to_join[0]->player2)){
            DB::table('games')->where('name', $roomname)->update(['player2' => $playername]);
            return 'Game is starting, it is Player 1\'s turn';
        }else{
            return 'Error: Room is already full';
        }
        return $game_to_join;
    }
    public function leave_room(){
        $playername = Input::get('name');
        $roomname =  Input::get('room_name');
        $game_to_leave = DB::select('select * from games where name = ?',[$roomname]);
        if( empty( $game_to_join ) ) {
            return 'Error: Room does not exist';
        }
        if( empty( $playername ) ) {
            return 'Error: Send a valid username';
        }
        if(strcmp($game_to_join[0]->player1,$playername)){
            DB::table('games')->where('name', $roomname)->update(['player1' => null]);
            return 'Waiting for other player to join';
        }else if(empty($game_to_join[0]->player2)){
            DB::table('games')->where('name', $roomname)->update(['player2' => $playername]);
            return 'Game is starting, it is Player 1\'s turn';
        }else{
            return 'Error: Room is already full';
        }
    }
}



























