<?php
set_time_limit(0);
function makeCurl($method,$datas=[])
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
function levelFinder()
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
function begin()
{
  global $chat_id;
  makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"شما ثبت نام نکردید.ابتدا ثبت نام کنید",'reply_markup'=>json_encode(['inline_keyboard'=>[[['text'=>"ثبت نام",'callback_data'=>'signup/*+12']]]])]);
}
function beginSignup()
{
  global $chat_id;
  global $userfirstname;
  $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
  mysqli_query($db,"INSERT INTO message (userid,userfirstname,level) VALUES ({$chat_id},\"{$userfirstname}\",1)");
  makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"نام خود را وارد کنید:"]);
}
function f_name()
{
    global $chat_id;
    global $userfirstname;
    global $text;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,first_name) VALUES ({$chat_id},\"{$userfirstname}\",2,\"{$text}\")");
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"نام خانوادگی خود را وارد کنید:"]);
}
function l_name()
{
    global $chat_id;
    global $userfirstname;
    global $text;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,last_name) VALUES ({$chat_id},\"{$userfirstname}\",3,\"{$text}\")");
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"ایمیل خود را وارد کنید:"]);
}
function endSingup()
{
    global $chat_id;
    global $userfirstname;
    global $level;
    $firsname;
    $lsatname;
    $email;
    $password;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    $result3=mysqli_query($db,"SELECT * FROM message WHERE userid={$chat_id}");
    while($row3 = mysqli_fetch_array($result2))
    {
        if($row3['level'] == 2)
            $firstname = $row3['first_name'];
        if($row3['level'] == 3)
            $lsatname = $row3['last_name'];
        if($row3['level'] == 4)
            $email = $row3['email'];
        if($row3['level'] == 5)
            $password = $row3['password'];
    }
    mysqli_query($db,"DELETE FROM message WHERE userid = {$chat_id}");
    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,password,first_name,last_name,email,credit) VALUES ({$chat_id},\"{$userfirstname}\",6,\"{$password}\",\"{$firstname}\",\"{$lastname}\",\"{$email}\",10)");
    $level = 6;
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"ثبت نام شما با موفقیت انجام شد."]);
}
function e_mail()
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
function pass_word()
{
    global $chat_id;
    global $userfirstname;
    global $text;
    $db=mysqli_connect("sql209.gigfa.com","gigfa_18319095","14127576","gigfa_18319095_bot1");
    mysqli_query($db,"INSERT INTO message (userid,userfirstname,level,password) VALUES ({$chat_id},\"{$userfirstname}\",5,\"{$text}\")");
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>"رمز خود را تایید کنید:"]);
}
function confirmPass()
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
    makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>$password]);
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
function menu()
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
        ['text'=>'ارسال مطلب','callback_data'=>'SendDD@Dd']
      ]
    ]
    ])]);
}

function handleUser()
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
          makeCurl("sendMessage",["chat_id"=>$chat_id,"text"=>$level]);
          if($level < 6)
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

        }
      }
      sleep(5);
      handleUser();
}
handleUser();
