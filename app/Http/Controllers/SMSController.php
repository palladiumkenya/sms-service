<?php

namespace App\Http\Controllers;

use App\Models\Sms;
use App\Models\Apps;
use App\Models\Blacklist;
use App\Models\Settings;
use Illuminate\Http\Request;
use AfricasTalking\SDK\AfricasTalking;

class SMSController extends Controller
{
  

      public function __construct()
    {
      
        $this->middleware('auth', ['only' => [
            'sendSMS',
            'checkBlacklist'
        ]]);

        
    }

    public function sendSMS(Request $request)
    {   //Check if valid JSON and all parameters are filled

       $this->validate($request, [
            'destination' => 'required',
            'msg' => 'required',
            'sender_id' => 'required',
            'gateway' => 'required'
        ]);

       $tel=$request['destination'];
       //Fetch API ID details
       $header = $request->header('api-token');
       $api_id=Apps::where('api_key', $header)->first();
      // return response()->json($api_id , 200);

       //return 

        //Validate If number is in Blacklist
        $exists= Blacklist::where('telephone', 'LIKE', substr($tel, -9))->count(); //0 720 000 000
        
        //Save Message Request
        if($exists=='0')
        {
            //Send to ATK
            //fetch ATK gateway parameters
            $atk_param= Settings::where('gate_way', 'LIKE', $request->gateway)->get();

             $username = $atk_param[0]['user']; // Set AT User
            $apiKey   = $atk_param[0]['api_key']; // Set AT Key
             $AT       = new AfricasTalking($username, $apiKey);

                // Get one of the services
                $sms      = $AT->sms();
                // Use the service
                $result   = $sms->send([
                   'to'      => $request->destination,
                   'message' => $request->msg,
                   'from'=> $request->gateway
                ]);
              
               $data=json_decode(json_encode($result), true);
                
                //Log SMS with ATK status
                $sms = new Sms;
                $sms->destination = $request->destination;
                $sms->msg = $request->msg;
                $sms->received_date =  date('Y-m-d H:i:s');
                $sms->sender_id = $request->sender_id;
                $sms->gateway = $request->gateway;
                $sms->internal_status='sent'; 
                $sms->app_id=$api_id['tbl_app_id'];
                $sms->cost=$data['data']['SMSMessageData']['Recipients'][0]['cost'];
                $sms->msg_id=$data['data']['SMSMessageData']['Recipients'][0]['messageId'];
                $sms->message_status=$data['data']['SMSMessageData']['Recipients'][0]['status'];
                $saved_sms= $sms->save();


                return response()->json($result , 200);
                exit();

        }else 
        {
            //Log SMS without sending to ATK
            $sms = new Sms;
            $sms->destination = $request->destination;
            $sms->msg = $request->msg;
            $sms->received_date =  date('Y-m-d H:i:s');
            $sms->sender_id = $request->sender_id;
            $sms->gateway = $request->gateway;
            $sms->app_id=$api_id['tbl_app_id'];
            $sms->internal_status='Not Sent'; 
            $sms->message_status='Blacklisted';
            $saved_sms= $sms->save();

            return response()->json(array('status'=>'blacklisted', 'data'=>array('SMSMessageData'=>array('Message'=>'','Recipients'=>array()))) , 200);
            exit();

        }

       
    }

    public function checkBlacklist($id)
    {
        //Check if in blacklist model
        $exists= Blacklist::where('telephone', 'LIKE', substr($tel, -9))->count();
        if($exists=='0')
        {
            return response()->json(array('success'=>true, 'message'=>'NotBlacklisted'),200);

        }else
        {
            return response()->json(array('success'=>true, 'message'=>'Blacklisted'),200);

        }
    }

    public function SMSReports($id)
    {

        return response()->json(Author::find($id));
    }

    public function CallBack(Request $request)
    {
        //Identify the Receiving App
        $c_msg_id=$request['id'];
        $c_status=$request['status'];
        $c_number=$request['phoneNumber'];
        $c_code=$request['networkCode'];
        $c_reason=$request['failureReason'];
        $c_count=$request['retryCount'];

        //Update SMS Data Model on the New Status 
        Sms::where('msg_id',  $c_msg_id)->update(array('message_status' =>  $c_status, 'callback_status' =>  $c_status, 'n_code' =>  $c_code,'f_reason' =>  $c_reason,'callback_date'=>date('Y-m-d H:i:s')));

        //Fetch Callback URL for requesting Application
        $cback_api= Sms::where('msg_id', 'LIKE', $c_msg_id)->get();
        $app_id = $cback_api[0]['app_id']; // Set app_id User

        //Fetch CallBackURL
        $c_backurls=Apps::where('tbl_app_id', $app_id)->first();
        $c_back_url = $c_backurls['callback_url']; // Set Call Back URL
        $c_enabled = $c_backurls['c_enabled']; // Set Call Back Enabled
        
        if($c_enabled=='1')
        {

            //Call call back URL
            $client = new \GuzzleHttp\Client();
        $response = $client->request('POST',$c_back_url,  [
            'id' =>  $c_msg_id,
                'status' => $c_status,
                'phoneNumber' => $c_number,
                'networkCode' => $c_code,
                    'failureReason' =>  $c_reason,
                    'retryCount' => $c_count
                
            ]); 

        }

        //If blacklisted Save in the Blacklist Model
        if(trim($c_reason)=='UserInBlackList')
        {
             $b_list = new Blacklist;
                        $b_list->telephone = substr($c_number, -9);
                        $b_list->app_id = $app_id;
                        $b_list->date_added =  date('Y-m-d H:i:s');
                        $b_list->sms_id = $c_msg_id;
                        $b_list->b_status = 1;
                        $saved_b_list= $b_list->save();

        }
        
        //Receive Callback
         return response()->json(array('success'=>true, 'message'=>'Request Received Successfully'),200);
       
    }

    
}