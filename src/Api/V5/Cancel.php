<?php
/**
 * @author lin <465382251@qq.com>
 * */

namespace Lin\Bybit\Api\V5;

use Lin\Bybit\RequestV5Cancel;

class Cancel extends RequestV5Cancel
{
    

    /*
     *POST /v5/order/cancel
     * */
    public function postCancel(array $data=[]){
        $this->type='POST';
        $this->path='/v5/order/create';
        $this->data=$data;
        return $this->exec();
    }

  
}
