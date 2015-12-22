<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Input;
use DB;
use Validator;
use Hash;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class APIController extends Controller
{
    public function __construct() 
    {
       // Apply the jwt.auth middleware to all methods in this controller
       // except for the authenticate method. We don't want to prevent
       // the user from retrieving their token if they don't already have it
       $this->middleware('jwt.auth', ['except' => ['createUser', 'authenticateUser', 'verifyToken']]);
    }

    /**
     * Registers a user, given a name, unique password, and unique email
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:users|max:127',
            'password' => 'required',
            'email'    => 'required|email|unique:users',
        ]);

        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        else {
            $user = new \App\User;
            $user->name = $request['name'];
            $user->password = Hash::make($request['password']);
            $user->email = $request['email'];
            $user->save();
            $token = JWTAuth::fromUser($user, array());
            return array('success', $token);
        }
    }

    /**
     * Authenticates a user, given a name and password
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function authenticateUser(Request $request) {
        $credentials = $request->only('name', 'password');
        $validator = Validator::make($credentials, [
            'name' => 'required|max:127',
            'password'   => 'required',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        else {
            try {
                // attempt to verify the credentials and create a token for the user
                if (! $token = JWTAuth::attempt($credentials)) {
                    return response()->json(['error' => 'invalid_credentials'], 401);
                }
            } catch (JWTException $e) {
                // something went wrong whilst attempting to encode the token
                return response()->json(['error' => 'could_not_create_token'], 500);
            }
            // all good so return the token
            return response()->json(compact('token'));
        }
    }

    /**
     * Display a listing of the resource.
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function create_room(Request $request) {
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



























