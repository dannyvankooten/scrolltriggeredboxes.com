<?php

namespace App\Contentful\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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
		$result = Contentful::entries()
	                    ->limitByType($this->id)
	                    ->where('fields.slug', '=', $url)
	                    ->limit(1)
	                    ->get();

		if( $result['items'] ) {
			$result = $result['items'][0];
			$model = $this->getModel($result);
			return $model;
		}

		return null;
	}

}