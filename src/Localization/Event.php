<?php
/**
 * File containing the {@link Localization_Event} class.
 * @package Localization
 * @subpackage Events
 * @see Localization_Event
 */

declare(strict_types=1);

namespace AppLocalize;

/**
 * Base class for triggered event instances.
 *
 * @package Localization
 * @subpackage Events
 * @author Sebastian Mordziol <s.mordziol@mistralys.eu>
 * @see Localization::triggerEvent()
 */
abstract class Localization_Event
{
   /**
    * @var array
    */
    protected $args;
    
    public function __construct(array $args)
    {
        $this->args = $args;
    }

   /**
    * Fetches the argument at the specified index in the 
    * event's arguments list, if it exists. 
    * 
    * @param int $index Zero-based index number.
    * @return mixed|NULL
    */ 
    public function getArgument(int $index)
    {
        if(isset($this->args[$index])) {
            return $this->args[$index];
        }
        
        return null;
    }
}
