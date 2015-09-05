<?php

namespace App\Contentful\Repositories;

use Incraigulous\Contentful\Facades\Contentful;

class PluginRepository {

	protected $id = '6U2uGLyCnmQ6Oaqoe60Iiu';

	/**
	 * Get an entry model from Contentful by the "url" field.
	 * @param $url
	 * @return array
	 */
	public function findByUrl( $url )
	{
		$result = Contentful::entries()
	                    ->limitByType($this->id)
	                    ->where('fields.slug', '=', $url)
	                    ->limit(1)
	                    ->get();

		if( isset( $result['items'][0]['fields'] ) ) {
			return $result['items'][0]['fields'];
		}

		return null;
	}

}