<?php
set_time_limit(0);
function makeCurl($method,$datas=[])    //macke and receive request to bot
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,"https://api.telegram.org/bot233968556:AAHnL3AjEhslHhzJVUnd_xwAUE5lnMQPW80/{$method}");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($datas));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $server_output = curl_exec ($ch);
    curl_close ($ch);
    return $server_output;
}
$last_updated_id=0;
$chat_id;
$text;
$userfirstname;
$level;
function levelFinder()    //find user's level and return it
{
    global $chat_id;
    $level2 = 0;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    $result2=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
    while($row1 = mysqli_fetch_array($result2))
    {
        if($row1['level'] > $level2)
          $level2 = $row1['level'];
    }
    return $level2;
}
function begin()       //generate button for signup
{
  global $chat_id;
  makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"شما ثبت نام نکردید.ابتدا ثبت نام کنید",'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"ثبت نام",'callback_data'=>'signup/*+12']]]])]);
}
function beginSignup()     //called when user use signup button for signup and ask user to enter first Name ,level 1 inserted
{
  global $chat_id;
  global $userfirstname;
  $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
  mysqli_query($db,"INSERT INTO message (userid,userfirstname,level) VALUES ({$chat_id},\"{$userfirstname}\",1)");
  makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"نام خود را وارد کنید:"]);
}
function f_name()    //insert fisrt name to database and ask for last name , level 2 inserted
{
    global $chat_id;
    global $userfirstname;
    global $text;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,first_name) VALUES ({$chat_id},\"{$userfirstname}\",2,\"{$text}\")");
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"نام خانوادگی خود را وارد کنید:"]);
}
function l_name()          //insert last name to database and ask for email , level 3 inserted
{
    global $chat_id;
    global $userfirstname;
    global $text;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,last_name) VALUES ({$chat_id},\"{$userfirstname}\",3,\"{$text}\")");
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"ایمیل خود را وارد کنید:"]);
}
function endSingup()        //complete the signup and clean database :) , level 6 inserted and default credit take action
{
    global $chat_id;
    global $userfirstname;
    global $level;
    $firsname;
    $lsatname;
    $email;
    $password;
    $credit;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    $result3=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
    while($row3 = mysqli_fetch_array($result3))
    {
        if($row3['level'] == 2)
            $firstname = $row3['first_name'];
        if($row3['level'] == 3)
            $lastname = $row3['last_name'];
        if($row3['level'] == 4)
            $email = $row3['email'];
        if($row3['level'] == 5)
            $password = $row3['password'];
    }
    $result4=mysqli_query($db,"SELECT * FROM DeletedAcount WHERE userid = {$chat_id}");
    if($row4 = mysqli_fetch_array($result4))
    {
        $credit = $row4['credit'];
        mysqli_query($db,"DELETE FROM DeletedAcount WHERE userid = {$chat_id}");
    }else{
      $credit = 10;
    }
    mysqli_query($db,"DELETE FROM message WHERE userid = {$chat_id}");
    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,password,first_name,last_name,email,credit) VALUES ({$chat_id},\"{$userfirstname}\",6,\"{$password}\",\"{$firstname}\",\"{$lastname}\",\"{$email}\",{$credit})");
    $level = 6;
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"ثبت نام شما با موفقیت انجام شد."]);
}
function e_mail()          //insert email to database and ask for password ,level 4 inserted
{
    global $chat_id;
    global $userfirstname;
    global $text;
    global $level;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    if($text == 'DeleteSignUpInTheMiddle')
    {
      mysqli_query($db,"DELETE FROM message WHERE userid = {$chat_id}");
      makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"ثبت نام حذف شد."]);
    }else
     {
    $p1 = 0;
    $result2=mysqli_query($db,"SELECT * FROM message WHERE email=\"{$text}\"");
    while($row1 = mysqli_fetch_array($result2))
    {
        $p1 = 1;
    }
    if($p == 1)
    {
      makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"این ایمیل قبلا ثبت شده است.لطفا ایمیل دیگری وارد کنید:",'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"حذف ثبت نام.",'callback_data'=>'DeleteSignUpInTheMiddle']]]])]);
    }
    else if($p1 == 0)
    {
        mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,email) VALUES ({$chat_id},\"{$userfirstname}\",4,\"{$text}\")");
        makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"ایمیل شما تایید شد."]);
        makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"رمز خود را وارد کنید:"]);
    }
  }
}
function pass_word()       //insert password to database and ask for confirmation , level 5 inserted
{
    global $chat_id;
    global $userfirstname;
    global $text;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,password) VALUES ({$chat_id},\"{$userfirstname}\",5,\"{$text}\")");
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"رمز خود را تایید کنید:"]);
}
function confirmPass()     //confirm password and call endSignup funstion , no level inserted but called endSingup function
{
    global $chat_id;
    global $text;
    $password;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    $result3=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
    while($row3 = mysqli_fetch_array($result3))
    {
        if($row3['level'] == 5)
            $password = $row3['password'];
    }
    if($text == $password)
    {
      makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"رمز شما تایید شد."]);
      endSingup();
    }else
    {
      makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"رمز شما تایید نشد.دوباره تلاش کنید."]);
      makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"رمز خود را وارد کنید:"]);
      mysqli_query($db,"DELETE FROM message WHERE userid = {$chat_id} and level = 5");
    }
}
function menu()             //just show menu and send encoded text as request to bot
{
    global $chat_id;
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"انتخاب کنید.",
    'reply_markup'=>json_encode(
    ['inline_keyboard'=>[
      [
        ['text'=>"ویرایش اکانت",'callback_data'=>'EditT@T']
      ],
      [
        ['text'=>"حذف اکانت",'callback_data'=>'DELEte@E']
      ],
      [
        ['text'=>'مشاهده اکانت','callback_data'=>'SeE@EE']
      ],
      [
        ['text'=>'افزایش اعتبار','callback_data'=>'CreDit@@TTT']
      ],
      [
        ['text'=>'اضافه کردن کانال','callback_data'=>'ChANELl@lL']
      ],
      [
        ['text'=>'حذف کردن کانال','callback_data'=>'CHANEllDeleTET@#$TT']
      ],
      [
        ['text'=>'ارسال مطلب','callback_data'=>'SendDD@Dd']
      ]
    ]
    ])]);
}
function deletE()           //delete your account ,level 7 may be inserteb or deleted,connect to DeletedAcount table
{
    global $level;
    global $text;
    global $chat_id;
    $password;
    $credit;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    if($level == 6)
    {
        mysqli_query($db,"INSERT INTO message (userid,userfirstname,level) VALUES ({$chat_id},\"{$userfirstname}\",7)");
        makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"رمز خود را وارد کنید:"]);
    }else if($level == 7)
    {
        $result3=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
        while($row3 = mysqli_fetch_array($result3))
        {
            if($row3['level'] == 6)
                $password = $row3['password'];
        }
        if($password == $text)
        {
            $result3=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
            while($row3 = mysqli_fetch_array($result3))
            {
                  if($row3['level'] == 6)
                      $credit = $row3['credit'];
            }
            mysqli_query($db,"INSERT INTO DeletedAcount (userid,credit) VALUES ({$chat_id},{$credit})");
            mysqli_query($db,"DELETE FROM message WHERE userid = {$chat_id}");
            mysqli_query($db,"DROP TABLE `bot{$userid}`");
            makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"اکانت شما حذف شد."]);
        }else {
            makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"رمز اشتباه است."]);
            mysqli_query($db,"DELETE FROM message WHERE userid = {$chat_id} and level = 7");
            menu();
        }
    }
}
function show()             //just show your detail
{
    global $chat_id;
    $firsname;
    $lsatname;
    $email;
    $credit;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    $result3=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
    while($row3 = mysqli_fetch_array($result3))
    {
            $firstname = $row3['first_name'];
            $lastname = $row3['last_name'];
            $email = $row3['email'];
            $credit = $row3['credit'];
    }
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"نام شما :{$firstname}"]);
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"نام خانوادگی شما : {$lastname}"]);
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"ایمیل شما :{$email}"]);
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"اعتبار شما :{$credit}"]);
}
function edit()               //edit user account.Most "COMPLEX" Function. **WARNING:SHOULD WRITE EDIT PASSWORD PART.**
{
    global $chat_id;
    global $level;
    global $text;
    $hlevel;
    $firstname;
    $lastname;
    $email;
    $password;
    $credit;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    if($level == 6)                //send first request for editini
    {
        makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"انتخاب کنید.",
        'reply_markup'=>json_encode(
        ['inline_keyboard'=>[
          [
            ['text'=>"ویرایش نام",'callback_data'=>'EdiTEeFirSG%T']
          ],
          [
            ['text'=>"ویرایش نام خانوادگی",'callback_data'=>'EdiDf23DLaS$T']
          ],
          [
            ['text'=>'ویرایش ایمیل','callback_data'=>'EdiVASDTEM#@$I%$&L']
          ],
          [
            ['text'=>'ویرایش رمز','callback_data'=>'EdiTTTPAssw000rd']
          ],
          [
            ['text'=>'بستن ویرایش','callback_data'=>'CloSE@!#$!EdiG5T']
          ]
        ]
        ])]);
        mysqli_query($db,"INSERT INTO message (userid,userfirstname,level) VALUES ({$chat_id},\"{$userfirstname}\",8)");
    }elseif($level == 8 )
    {
        $result3=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
        while($row3 = mysqli_fetch_array($result3))
        {
            if($row3['level'] == 8)
                $hlevel = $row3['hlevel'];
        }
        if($hlevel == null)
        {
          if($text == 'CloSE@!#$!EdiG5T')
          {
              mysqli_query($db,"DELETE FROM message where userid = {$chat_id} and level = 8");
              makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"ویرایش بسته شد."]);
              menu();
          }
          if($text == 'EdiTEeFirSG%T')
          {
              makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"نام جدید خود را وارد کنید."]);
              mysqli_query($db,"DELETE FROM message where userid = {$chat_id} and level = 8");
              mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,hlevel) VALUES ({$chat_id},\"{$userfirstname}\",8,\"firstname\")");
          }
          if($text == 'EdiDf23DLaS$T')
          {
              makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"نام خانوادگی جدید خود را وارد کنید."]);
              mysqli_query($db,"DELETE FROM message where userid = {$chat_id} and level = 8");
              mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,hlevel) VALUES ({$chat_id},\"{$userfirstname}\",8,\"lastname\")");
          }
          if($text == 'EdiVASDTEM#@$I%$&L')
          {
              makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"ایمیل جدید خود را وارد کنید."]);
              mysqli_query($db,"DELETE FROM message where userid = {$chat_id} and level = 8");
              mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,hlevel) VALUES ({$chat_id},\"{$userfirstname}\",8,\"email\")");
          }
          if($text == 'EdiTTTPAssw000rd')
          {
              makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"رمز کنونی خود را وارد کنید."]);
              mysqli_query($db,"DELETE FROM message where userid = {$chat_id} and level = 8");
              mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,hlevel) VALUES ({$chat_id},\"{$userfirstname}\",8,\"password\")");
          }
        }
        elseif($hlevel == "firstname")
        {
            mysqli_query($db,"DELETE FROM message where userid = {$chat_id} and level = 8");
            $result3=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
            while($row3 = mysqli_fetch_array($result3))
            {
                $firstname = $row3['first_name'];
                $lastname = $row3['last_name'];
                $credit = $row3['credit'];
                $password = $row3['password'];
                $email = $row3['email'];
            }
            mysqli_query($db,"DELETE FROM message where userid = {$chat_id}");
            mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,password,first_name,last_name,email,credit) VALUES ({$chat_id},\"{$userfirstname}\",6,\"{$password}\",\"{$text}\",\"{$lastname}\",\"{$email}\",{$credit})");
            makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"نام شما ویرایش شد."]);
            menu();
        }
        elseif ($hlevel == "lastname") {
            mysqli_query($db,"DELETE FROM message where userid = {$chat_id} and level = 8");
            $result3=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
            while($row3 = mysqli_fetch_array($result3))
            {
                $firstname = $row3['first_name'];
                $lastname = $row3['last_name'];
                $credit = $row3['credit'];
                $password = $row3['password'];
                $email = $row3['email'];
            }
            mysqli_query($db,"DELETE FROM message where userid = {$chat_id}");
            mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,password,first_name,last_name,email,credit) VALUES ({$chat_id},\"{$userfirstname}\",6,\"{$password}\",\"{$firstname}\",\"{$text}\",\"{$email}\",{$credit})");
            makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"نام خانوادگی شما ویرایش شد."]);
            menu();
        }
        elseif($hlevel == "email")
        {
            mysqli_query($db,"DELETE FROM message where userid = {$chat_id} and level = 8");
            $result3=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
            while($row3 = mysqli_fetch_array($result3))
            {
                $firstname = $row3['first_name'];
                $lastname = $row3['last_name'];
                $credit = $row3['credit'];
                $password = $row3['password'];
                $email = $row3['email'];
            }
            mysqli_query($db,"DELETE FROM message where userid = {$chat_id}");
            mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,password,first_name,last_name,email,credit) VALUES ({$chat_id},\"{$userfirstname}\",6,\"{$password}\",\"{$firstname}\",\"{$lastname}\",\"{$text}\",{$credit})");
            makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"ایمیل شما ویرایش شد."]);
            menu();
        }
  }
}

function handleUser()                        //main body
{
    global $last_updated_id;
    global $chat_id;
    global $text;
    global $userfirstname;
    global $level;
    $updates = json_decode(makeCurl("getUpdates",["offset"=>($last_updated_id+1)]));
    if($updates->ok == true && count($updates->result) > 0)
    {
        foreach($updates->result as $update)
        {
          $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
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
    			$level = 0;
          $level = levelFinder();
          if($level < 6)         //signup process
          {
              if($level == 0 && $text != "signup/*+12")
                {
                    begin();
                    continue;
                }
              if($text == "signup/*+12" && $level == 0)
                {
                  beginSignup();
                  continue;
                }
              if ($level == 1)
              {
                  f_name();
                  continue;
                }
              if($level == 2)
              {
                l_name();
                continue;
              }
              if($level == 3)
              {
                  e_mail();
                  continue;
              }
              if($level == 4)
              {
                  pass_word();
                  continue;
              }
              if($level == 5);
                  confirmPass();
              if($level == 6)
                  menu();
            }
          elseif($level >= 6)    //using bot when signup is complete
          {
              if(  ( $level == 6 && $text == 'DELEte@E' ) || $level == 7 )
                  deletE();
              elseif($level == 6 && $text == 'SeE@EE')
              {
                  show();
                  menu();
              }
              elseif(  ( $level == 6 && $text == 'EditT@T' ) || $level == 8 )
                  edit();
              else{
                menu();
                  }
          }
        }
      }
      sleep(5);
      handleUser();
}
handleUser();
