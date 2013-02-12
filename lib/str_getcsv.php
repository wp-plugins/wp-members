<?php
/**
 * Replace str_getcsv()
 *
 * PHP versions 4 and 5
 *
 * @category  PHP
 * @package   PHP_Compat
 * @license   LGPL - http://www.gnu.org/licenses/lgpl.html
 * @copyright 2004-2009 Aidan Lister <aidan@php.net>, Arpad Ray <arpad@php.net>
 * @link      http://php.net/function.str_getcsv
 * @author    HM2K <hm2k@php.net>
 * @version   $CVS: 1.0 $
 * @since     5.3.0
 * @require   PHP 4.0.0 (fgetcsv)
 */

if ( ! function_exists( 'php_compat_str_getcsv' ) ) {

	function php_compat_str_getcsv($input, $delimiter = ',', $enclosure = '"', $escape = '\\') {
		$fh = tmpfile();
		fwrite($fh, $input);
		$data = array();
		while (($row = php_compat_fgetcsv_wrap($fh, 1000, $delimiter, $enclosure, $escape)) !== FALSE) {
			$data[] = $row;
		}
		fclose($fh);
		return empty($data) ? false : $data;
	}
	
}


if ( ! function_exists( 'php_compat_fgetcsv_wrap' ) ) {
	/**
	 * Wraps fgetcsv() for the correct PHP version
	 *
	 * @link http://php.net/function.fgetcsv
	 */
	function php_compat_fgetcsv_wrap($fh, $length, $delimiter = ',', $enclosure = '"', $escape = '\\') {
		// The escape parameter was added
		if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
			return fgetcsv($fh, $length, $delimiter, $enclosure, $escape);
		}
		// The enclosure parameter was added
		elseif (version_compare(PHP_VERSION, '4.3.0', '>=')) {
			return fgetcsv($fh, $length, $delimiter, $enclosure);
		} else {
			return fgetcsv($fh, $length, $delimiter);
		}
	}
}


if ( ! function_exists( 'str_getcsv' ) ) {
    /**
     * Backwards compatbility for str_getcsv()
     *
     * @link http://php.net/function.fgetcsv
     */
    function str_getcsv($input, $delimiter = ',', $enclosure = '"', $escape = '\\') {
        return php_compat_str_getcsv($input, $delimiter, $enclosure, $escape);
    }
}
?>