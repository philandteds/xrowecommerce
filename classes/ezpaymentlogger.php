<?php
/**
 * File containing the eZPaymentLogger class.
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package kernel
 */

/*!
  \class eZPaymentLogger
*/

class eZPaymentLogger
{
    function eZPaymentLogger( $fileName, $mode )
    {
        $this->file = fopen( $fileName, $mode );
    }

    static function CreateNew($fileName)
    {
        return new eZPaymentLogger( $fileName, "wt" );
    }

    static function CreateForAdd($fileName)
    {
        return new eZPaymentLogger( $fileName, "a+t" );
    }

    function writeString( $string, $label='' )
    {
        if( $this->file )
        {
            if ( is_object( $string ) || is_array( $string ) )
                $string = eZDebug::dumpVariable( $string );

            if( $label == '' )
                fputs( $this->file, self::sessionID() . '  ' . $string."\r\n" );
            else
                fputs( $this->file, self::sessionID() . '  ' . $label . ': ' . $string."\r\n" );
        }
    }

    function writeTimedString( $string, $label='' )
    {
        if( $this->file )
        {
            $time = $this->getTime();

            if ( is_object( $string ) || is_array( $string ) )
                $string = eZDebug::dumpVariable( $string );

            if( $label == '' )
                fputs( $this->file, $time. '  '. self::sessionID() . '  ' . $string. "\n" );
            else
                fputs( $this->file, $time. '  '. self::sessionID() . '  ' . $label. ': '. $string. "\n" );
        }
    }

    static function getTime()
    {
        $time = strftime( "%d-%m-%Y %H-%M" );
        return $time;
    }

    static function sessionID() {
        if (eZSession::hasStarted()) {
            return session_id();
        }
        return '';

    }

    public $file;
}
?>
