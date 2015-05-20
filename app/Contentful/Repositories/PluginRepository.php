<?php

namespace App\Contentful\Repositories;

use Incraigulous\Contentful\EntriesRepositoryBase;
use Incraigulous\Contentful\Facades\Contentful;

class PluginRepository extends EntriesRepositoryBase {

	protected $id = '6U2uGLyCnmQ6Oaqoe60Iiu';

	/**
	 * Get an entry model from Contentful by the "url" field.
	 * @param $url
	 * @return Model
	 */
	public function findByUrl( $url )
	{
		return $this->getModel(
			Contentful::entries()
			          ->limitByType($this->id)
			          ->where('fields.slug', '=', $url)
			          ->limit(1)
			          ->get()
		);
	}

}