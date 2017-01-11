<?php

namespace ICheetah\Http\Session;

class DatabaseSessionHandler implements \SessionHandlerInterface
{
    public function close()
    {
        // do nothing
        return true;
    }

    public function destroy($session_id)
    {
        $model = \Models\Sessions::find($session_id);
        if ($model != null) {
            $model->delete();
            return true;
        } else {
            return false;
        }
    }

    public function gc($maxlifetime)
    {
        $old = time() - $maxlifetime;
        $model = new \Models\Sessions();
        $model->where(sprintf("access < %d", $old))->getAll();
        foreach ($model as $value) {
            $value->delete();
        }
        return true;
    }

    public function open($save_path, $name)
    {
        // do nothing
        return true;
    }

    public function read($session_id)
    {
        $model = \Models\Sessions::find($session_id);
        if ($model != null){
            return $model->data;
        } else {
            return "";
        }
    }

    public function write($session_id, $session_data)
    {
        if ($this->exists($session_id)){
            //Update record          
            $model = \Models\Sessions::find($session_id);
            if ($model != null){
                $model->app = Application::getAppName();
                $model->user_id = Users::getInstance()->activeUserID();
                $model->access = time();
                $model->data = $session_data;
                $model->save();
                //Debug::out("DB updated \n", true);
            }            
        } else {
            //Create record
            $model = new \Models\Sessions();
            $model->session_id = $session_id;
            $model->user_id = Users::getInstance()->activeUserID();
            $model->app = Application::getAppName();
            $model->access = time();
            $model->data = $session_data;
            $model->create();            
            //Debug::out("DB inserted\n", true);
        }
        @session_write_close();
        return true;
    }
    
    public function exists($session_id)
    {
        return \Models\Sessions::find($session_id) != null;
    }

}