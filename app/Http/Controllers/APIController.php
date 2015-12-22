<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Input;
use DB;
use \Firebase\JWT\JWT;
use Mockery\CountValidator\Exception;


class APIController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function postRegister(){
        $key = "thisisthekey";


        $username = Input::get('username');
        $password =  Input::get('password');
        if( empty( $username ) ) {
            return 'Error: Send a valid Username';
        }
        if( empty( $password ) ) {
            return 'Error: Send a valid password';
        }
        $usercheck = DB::select('select * from user where name = ?',[$username]);
        if( empty( $usercheck ) ){
            DB::insert('insert into user (name, password,currentroom,admin) values (?,?,?,?)', [$username, $password,null,0]);



            $token = array(
                "user" => $username,
                "pass" => $password
            );

            $jwt = JWT::encode($token, $key);



            return "Successfully added user\r\nYour user token is " . $jwt . "\r\nSend this with every command. Login with your credentials to receive a new token";
        }else{
            return 'Error: Username already exists';
        }
    }
    public function postLogin(){
        $key = "thisisthekey";

        $username = Input::get('username');
        $password =  Input::get('password');
        if( empty( $username ) ) {
            return 'Error: Send a valid Username';
        }
        if( empty( $password ) ) {
            return 'Error: Send a valid password';
        }
        $usercheck = DB::select('select * from user where name = ?',[$username]);

        if( empty( $usercheck ) ){
            return 'Error: Username does not exists';
        }else{
            if(strcmp($usercheck[0]->password,$password) == 0){

                $token = array(
                    "user" => $username,
                    "pass" => $password
                );

                $jwt = JWT::encode($token, $key);



                return "Successfully logged in\r\nYour user token is " . $jwt . "\r\nSend this with every command. Login with your credentials to receive a new token";
            }else{
                return 'Error: Password is incorrect';
            }
        }
    }
    public function verifyToken(){
        $key = "thisisthekey";
        $token = Input::get('token');
        if( empty( $token ) ){
            return [false,'wtf'];
        }




        try{
            $decoded = JWT::decode($token, $key, array('HS256'));     //TODO: fix the stupid ass exception thrown here when input is invalid
        }catch(Exception $e){
            return [false,'here'];
        }
        $username = $decoded->user;
        $password = $decoded->pass;




        $usercheck = DB::select('select * from user where name = ?',[$username]);
        if( empty( $usercheck ) ){
            return [false,'how'];
        }else{
            if(strcmp($usercheck[0]->password,$password) == 0){
                return [true,$username];
            }else{
                return [false,'wat'];
            }
        }
    }







    public function create_room(){
        //need to validate the user still
        if($this->verifyToken()[0] == false){
            return 'Error: Token invalid, sign in or create an account to receive a valid token';
        }

        $roomname =  Input::get('room_name');
        $games = DB::select('select * from games where name = ?',[$roomname]);
        if(empty($games)){
            DB::insert('insert into games (name, player1,player2,board,nextturn) values (?,?,?,?,?)', [$roomname, null,null,null,1]);
            return 'Successfully added room';
        }else{
            return 'Error, room already exists';
        }
    }






    public function join_room(){
        if($this->verifyToken()[0] == false){
            return 'Error: Token invalid, sign in or create an account to receive a valid token';
        }
        $username = $this->verifyToken()[1];
        $userindb = DB::select('select * from user where name = ?',[$username]);
        if(!empty($userindb[0]->currentroom)){
            return 'Error: User already in a game';
        }



        $roomname =  Input::get('room_name');
        $game_to_join = DB::select('select * from games where name = ?',[$roomname]);
        if( empty( $game_to_join ) ) {
            return 'Error: Room does not exist';
        }
        if(empty($game_to_join[0]->player1)){
            if(strcmp($game_to_join[0]->player2,$username) ==0){
                return 'Player is already in this game';
            }
            DB::table('games')->where('name', $roomname)->update(['player1' => $username]);
            DB::table('user')->where('name', $username)->update(['currentroom' => $roomname]);
            return 'Waiting for other player to join';
        }else if(empty($game_to_join[0]->player2)){
            if(strcmp($game_to_join[0]->player1,$username) ==0){
                return 'Player is already in this game';
            }
            DB::table('games')->where('name', $roomname)->update(['player2' => $username]);
            DB::table('user')->where('name', $username)->update(['currentroom' => $roomname]);
            return 'Game is starting, it is Player 1\'s turn';
        }else{
            return 'Error: Room is already full';
        }
        return $game_to_join;
    }
    public function leave_room(){
        if($this->verifyToken()[0] == false){
            return $this->verifyToken()[1];
            return 'Error: Token invalid, sign in or create an account to receive a valid token';
        }
        $username = $this->verifyToken()[1];
        $userindb = DB::select('select * from user where name = ?',[$username]);
        if( empty( $userindb ) ) {
            return 'Error: Didn\'t find the user in the db';
        }
        $currentroom = $userindb[0]->currentroom;
        if(empty($currentroom)){
            return 'Error: User is not in a room';
        }

        $game_to_leave = DB::select('select * from games where name = ?',[$currentroom]);
        if( empty( $game_to_leave ) ) {
            return 'Error: Room does not exist';
        }
        if(strcmp($game_to_leave[0]->player1,$username) == 0){
            var_dump( 'here');
            DB::table('games')->where('name',  $game_to_leave[0]->name)->update(['player1' => null]);
            DB::table('user')->where('name', $username)->update(['currentroom' => null]);
            return 'Left room';
        }else if(strcmp($game_to_leave[0]->player2,$username) == 0){
            var_dump('hwew2');
            DB::table('games')->where('name', $game_to_leave[0]->name)->update(['player2' => null]);
            DB::table('user')->where('name', $username)->update(['currentroom' => null]);
            return 'Left room';
        }
        return 'Error: User wasn\'t in the room';
    }
    public function delete_room(){
        if($this->verifyToken()[0] == false){
            return $this->verifyToken()[1];
            return 'Error: Token invalid, sign in or create an account to receive a valid token';
        }
        $username = $this->verifyToken()[1];
        $roomname = Input::get('room_name');
        $userindb = DB::select('select * from user where name = ?',[$username]);
        if( empty( $userindb ) ) {
            return 'Error: Didn\'t find the user in the db';
        }
        if( empty( $roomname ) ) {
            return 'Error: Enter a room name';
        }
        if($userindb->admin == 1){
            DB::table('users')->where('votes', '<', 100)->delete();
        }else{
            return 'Error: You need admin access to do that';
        }
    }
}



























