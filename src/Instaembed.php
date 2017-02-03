<?php

namespace Lagan\Property;

/**
 * Controller for the Lagan instaembed property.
 * The editor enters the identifier of an Instagram post.
 * The controller stores the Instagram embed code of an Instagram post in the database, after retrieving it using this Instagram identifier.
 * The embed code is stored in a seperate database filed, called [the name of the property]_embed
 *
 * A property type controller can contain a set, read, delete and options method. All methods are optional.
 * To be used with Lagan: https://github.com/lutsen/lagan
 */

class Instaembed {

	/**
	 * To get the data from the API, file_get_contents or cRUD is used.
	 * This function checks if one of them is available.
	 * If so, the data is retreived, else an error message is thrown.
	 *
	 * @param string	$url
	 *
	 * @return string	The result of the URL call.
	 */
	private function url_get_contents($url) {
		if (ini_get('allow_url_fopen') == 1) { 
			$output = file_get_contents($url);
		} elseif (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$output = curl_exec($ch);
			curl_close($ch);
		} else {
			throw new \Exception('allow_url_fopen or cURL has to be enabled on your webserver.');
		}
		return $output;
	}

	/**
	 * The set method is executed each time a property with this type is set.
	 *
	 * @param bean		$bean		The Redbean bean object with the property.
	 * @param array		$property	Lagan model property arrray.
	 * @param string	$new_value	The id of the Instagram post.
	 *
	 * @return string	The Instagram identifier.
	 */
	public function set($bean, $property, $new_value) {

		// Get embed code
		$result = json_decode(
			$this->url_get_contents('https://api.instagram.com/oembed?url=http://instagr.am/p/'.$new_value.'/?omitscript=true'),
			true
		);

		// $result is an array with these keys:
		// [provider_url]
		// [media_id]
		// [author_name]
		// [height]
		// [thumbnail_url]
		// [thumbnail_width]
		// [thumbnail_height]
		// [provider_name]
		// [title]
		// [html]
		// [width]
		// [version]
		// [author_url]
		// [author_id]
		// [type]

		// Store [html] embed code in database field named $property['name'].'_embed'
		$bean->{ $property['name'].'_embed' } = $result['html'];
		\R::store($bean);

		return $new_value;
	}

}

?>