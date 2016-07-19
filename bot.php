<?php
set_time_limit(0);

function makeCurl($method,$datas=[]){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.telegram.org/bot214855122:AAGvOydKFnDPXgzKkr0cdumQzYNpULMm9SI/{$method}");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($datas));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec ($ch);
    curl_close ($ch);
    return $server_output;
}

$last_updated_id=0;
function getUpdates(){
    global $last_updated_id;
    $updates = json_decode(makeCurl("getUpdates",["offset"=>($last_updated_id+1)]));
    if($updates->ok == true && count($updates->result) > 0){
        foreach($updates->result as $update){
      $db=mysqli_connect("localhost","root","MohandesPlus","test2");
      if($update->callback_query)
      {
        makeCurl("answerCallbackQuery",["callback_query_id" => $update->callback_query->id]);
        $text=$update->callback_query->data;
        $chat_id = $update->callback_query->from->id;
        $userfirstname=$update->callback_query->from->first_name;
      }else
      {
          $userfirstname=$update->message->chat->first_name;
          $text=$update->message->text;
          $chat_id = $update->message->chat->id;
      }
			$last_updated_id = $update->update_id;
      $result2=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
			$level = 0;
			$fname;
			$lname;
			$email;
			$usename;
			while($row1 = mysqli_fetch_array($result2))
			{
				if($row1['level'] > $level)
					$level = $row1['level'];
			}
			if((($level == 0) || ($level == 1 && $text != "signup")) && $level <2)
			{
				mysqli_query($db,"INSERT INTO message (userid,userfirstname,level) VALUES ({$chat_id},\"{$userfirstname}\",1)");
                makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"You Don't have Account.For Creating Account send signup:",'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"SIGNUP",'callback_data'=>'signup']]]])]);
			}
			if($level == 1 && $text == "signup" )
			{
				mysqli_query($db,"INSERT INTO message (userid,userfirstname,level) VALUES ({$chat_id},\"{$userfirstname}\",2)");
				makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Enter Your First Name:"]);
			}
			if($level == 2)
			{
				mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,first_name) VALUES ({$chat_id},\"{$userfirstname}\",3,\"{$text}\")");
				makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Enter Your Last Name:"]);
			}
			if ($level == 3)
			{
				mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,last_name) VALUES ({$chat_id},\"{$userfirstname}\",4,\"{$text}\")");
			   	makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Enter Your E-Mail:"]);
			}
			if ($level == 4)
			{
				mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,email) VALUES ({$chat_id},\"{$userfirstname}\",5,\"{$text}\")");
			   	makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Enter Your User Name:"]);
			}
			if ($level == 5 )
			{
                $result3=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
				while($row2 = mysqli_fetch_array($result3))
			    {
				if($row2['level'] == 3)
				{
					$fname = $row2['first_name'];
				}
				if($row2['level'] == 4)
				{
					$lname = $row2['last_name'];
				}
				if($row2['level'] == 5)
				{
					$email = $row2['email'];
				}
			    }
				mysqli_query($db,"DELETE FROM `message` WHERE userid = {$chat_id}");
				mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,username,first_name,last_name,email) VALUES ({$chat_id},\"{$userfirstname}\",6,\"{$text}\",\"{$fname}\",\"{$lname}\",\"{$email}\")");
				makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"SignUP Completed.",'reply_markup'=>json_encode(['inline_keyboard'=>[
				[
				['text'=>"EDIT Your Profile",'callback_data'=>'edit']
			    ],
				[
				['text'=>"DELETE Your Profile",'callback_data'=>'delete']
			    ],
				[
				['text'=>"SEE Your Profile",'callback_data'=>'show']
			    ]
                ]
                ]
                )]);
				}
            if($level == 6 && $text =="show")
			{
				$result4=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
				while($row3 = mysqli_fetch_array($result4))
			    {
					$fname = $row3['first_name'];
					$lname = $row3['last_name'];
					$email = $row3['email'];
					$usename = $row3['username'];
			    }
			   	makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Your First Name: {$fname} "]);
			   	makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Your Last Name: {$lname} "]);
			   	makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Your E-Mail: {$email} "]);
			   	makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Your UserName: {$usename} "]);
			}
			else if($level == 6 && $text =="delete")
			{
				mysqli_query($db,"DELETE FROM `message` WHERE userid = {$chat_id}");
			   	makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Your Account Deleted Successfuly ."]);
			}
			else if($level == 6 && $text =="edit")
			{
        makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Select ",'reply_markup'=>json_encode(['inline_keyboard'=>[
       [
       ['text'=>"EDIT Your First Name",'callback_data'=>'firstname']
         ],
       [
       ['text'=>"EDIT Your Last Name",'callback_data'=>'lastname']
         ],
       [
       ['text'=>"Edit Your UserName",'callback_data'=>'username']
         ],
         [
           ['text'=>"Edit Your E-Mail",'callback_data'=>'email']
         ]
               ]
               ]
               )]);
				$result4=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
				while($row3 = mysqli_fetch_array($result4))
			    {
					$fname = $row3['first_name'];
					$lname = $row3['last_name'];
					$email = $row3['email'];
					$usename = $row3['username'];
			    }
				mysqli_query($db,"DELETE FROM `message` WHERE userid = {$chat_id}");
				mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,username,first_name,last_name,email) VALUES ({$chat_id},\"{$userfirstname}\",7,\"{$usename}\",\"{$fname}\",\"{$lname}\",\"{$email}\")");
			}
			else if($level == 6)
			{
				makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"SignUP Completed.",'reply_markup'=>json_encode(['inline_keyboard'=>[
				[
				['text'=>"EDIT Your Profile",'callback_data'=>'edit']
			    ],
				[
				['text'=>"DELETE Your Profile",'callback_data'=>'delete']
			    ],
				[
				['text'=>"SEE Your Profile",'callback_data'=>'show']
			    ]
                ]
                ]
                )]);
			}
			if ($level == 7)
			{
				if($text == "firstname")
				{
					$result4=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
				    while($row3 = mysqli_fetch_array($result4))
			        {
					    $fname = $row3['first_name'];
					    $lname = $row3['last_name'];
					    $email = $row3['email'];
					    $usename = $row3['username'];
			        }
				    mysqli_query($db,"DELETE FROM `message` WHERE userid = {$chat_id}");
				    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,username,last_name,email) VALUES ({$chat_id},\"{$userfirstname}\",8,\"{$usename}\",\"{$lname}\",\"{$email}\")");
					makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Enter your new First Name"]);
				}
				if($text == "lastname")
				{
					$result4=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
				    while($row3 = mysqli_fetch_array($result4))
			        {
					    $fname = $row3['first_name'];
					    $lname = $row3['last_name'];
					    $email = $row3['email'];
					    $usename = $row3['username'];
			        }
				    mysqli_query($db,"DELETE FROM `message` WHERE userid = {$chat_id}");
				    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,username,first_name,email) VALUES ({$chat_id},\"{$userfirstname}\",9,\"{$usename}\",\"{$fname}\",\"{$email}\")");
					makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Enter your new Last Name"]);
				}
				if ($text == "email")
				{
					$result4=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
				    while($row3 = mysqli_fetch_array($result4))
			        {
				    	$fname = $row3['first_name'];
					    $lname = $row3['last_name'];
				    	$email = $row3['email'];
					    $usename = $row3['username'];
			        }
				    mysqli_query($db,"DELETE FROM `message` WHERE userid = {$chat_id}");
				    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,username,first_name,last_name) VALUES ({$chat_id},\"{$userfirstname}\",10,\"{$usename}\",\"{$fname}\",\"{$lname}\")");
					makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Enter your new E-Mail"]);
				}
				if($text == "username")
				{
					$result4=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
				    while($row3 = mysqli_fetch_array($result4))
			        {
					    $fname = $row3['first_name'];
					    $lname = $row3['last_name'];
					    $email = $row3['email'];
					    $usename = $row3['username'];
			        }
				    mysqli_query($db,"DELETE FROM `message` WHERE userid = {$chat_id}");
				    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,first_name,last_name,email) VALUES ({$chat_id},\"{$userfirstname}\",11,\"{$fname}\",\"{$lname}\",\"{$email}\")");
					makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Enter your new User Name"]);
				}
			}
			if($level == 8)
			{
				$result4=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
				while($row3 = mysqli_fetch_array($result4))
			        {
					    $lname = $row3['last_name'];
					    $email = $row3['email'];
					    $usename = $row3['username'];
			        }
				    mysqli_query($db,"DELETE FROM `message` WHERE userid = {$chat_id}");
				    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,username,last_name,email,first_name) VALUES ({$chat_id},\"{$userfirstname}\",6,\"{$usename}\",\"{$lname}\",\"{$email}\",\"{$text}\")");
					makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Your First Name Updated."]);
			}
			if ($level == 9)
			{
				$result4=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
				while($row3 = mysqli_fetch_array($result4))
			        {
					    $fname = $row3['first_name'];
					    $email = $row3['email'];
					    $usename = $row3['username'];
			        }
			    mysqli_query($db,"DELETE FROM `message` WHERE userid = {$chat_id}");
			    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,username,last_name,email,first_name) VALUES ({$chat_id},\"{$userfirstname}\",6,\"{$usename}\",\"{$text}\",\"{$email}\",\"{$fname}\")");
				makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Your Last Name Updated."]);
			}
			if ($level == 10)
			{
				$result4=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
				while($row3 = mysqli_fetch_array($result4))
			        {
					    $fname = $row3['first_name'];
					    $lname = $row3['last_name'];
					    $usename = $row3['username'];
			        }
				mysqli_query($db,"DELETE FROM `message` WHERE userid = {$chat_id}");
		        mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,username,last_name,email,first_name) VALUES ({$chat_id},\"{$userfirstname}\",6,\"{$usename}\",\"{$lname}\",\"{$text}\",\"{$fname}\")");
				makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Your E-Mail Updated."]);
			}
			if ($level == 11)
			{
				$result4=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
				while($row3 = mysqli_fetch_array($result4))
			        {
					    $fname = $row3['first_name'];
					    $lname = $row3['last_name'];
					    $email = $row3['email'];
			        }
				   mysqli_query($db,"DELETE FROM `message` WHERE userid = {$chat_id}");
				   mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,username,last_name,email,first_name) VALUES ({$chat_id},\"{$userfirstname}\",6,\"{$text}\",\"{$lname}\",\"{$email}\",\"{$fname}\")");
					makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"Your User Name Updated."]);
			}
        }
    }

    sleep(1);
    getUpdates();
}

getUpdates();
