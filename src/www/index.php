<?php



require_once 'init.php';

try {

       if ($_COOKIE['remember'] && \App\System::getUser()->user_id == 0) {
            $arr = explode('_', $_COOKIE['remember']);
            $_config = parse_ini_file(_ROOT . 'config/config.ini', true);
            if ($arr[0] > 0 && $arr[1] === md5($arr[0] . $_config['common']['salt'])) {
                $user = \App\Entity\User::load($arr[0]);
            }

            if ($user instanceof \App\Entity\User) {


                \App\System::setUser($user);

                $_SESSION['user_id'] = $user->user_id; //для  использования  вне  Application
                $_SESSION['userlogin'] = $user->userlogin; //для  использования  вне  Application
            }   

        }

    $app = new \App\Application('\App\Pages\Main');
    
    $app->Run();



    /* } catch (\ZippyERP\System\Exception $e) {
      Logger::getLogger("main")->error($e->getMessage(), e);
      \ZippyERP\System\Application::Redirect('\\ZippyERP\\System\\Pages\\Error', $e->getMessage());
      } catch (\Zippy\Exception $e) {
      Logger::getLogger("main")->error($e->getMessage(), e);
      \ZippyERP\System\Application::Redirect('\\ZippyERP\\System\\Pages\\Error', $e->getMessage());
      } catch (ADODB_Exception $e) {

      \ZippyERP\System\Application::Redirect('\\ZippyERP\\System\\Pages\\Error', $e->getMessage());
     */
} 
catch (Throwable $e) {
    if($e  instanceof ADODB_Exception){

       \ZDB\DB::getConnect()->CompleteTrans(false); // откат транзакции
    }
    $msg =    $e->getMessage() ;
    $logger->error($e);
    if($e  instanceof Error ){
        echo $e->getMessage().'<br>';
        echo $e->getLine().'<br>';
        echo $e->getFile().'<br>';
    }
}
catch (Exception $e) {    //для обратной совместимости
    if($e  instanceof ADODB_Exception){

       \ZDB\DB::getConnect()->CompleteTrans(false); // откат транзакции
    }
    $msg =    $e->getMessage() ;
    $logger->error($e);
   
}

