<?php

namespace ICheetah\MVC;

interface IRestFulActions
{
    
    /**
     * Lists records
     */
    public function index();
    
    /**
     * Shows record creation page
     */
    public function create();
    
    /**
     * Inserts new record
     */
    public function insert();
    
    /**
     * Shows record edit page
     */
    public function edit();
    
    /**
     * Updates record data
     */
    public function update();
    
    /**
     * Deletes a record
     */
    public function delete();

}

?>