<?php 
namespace Hawkiq\LaravelZaincash\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class ZainCashFacade
 * @package Hawkiq\ZainCash
 */
class ZainCashFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hawkiq-zaincash';
    }
}