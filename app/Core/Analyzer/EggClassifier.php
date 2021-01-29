<?php

namespace App\Core\Analyzer;

use Rubix\ML\PersistentModel;
use Rubix\ML\Datasets\Unlabeled;
use Rubix\ML\Persisters\Filesystem;

class EggClassifier
{
  public static function getLevel($image)
  {
    $dataset = new Unlabeled([[$image]]);
    $estimator = PersistentModel::load(new Filesystem(resource_path() . '/ml/egg-classification.model'));
    $predictions = $estimator->predict($dataset);
    return $predictions[0];
  }
}
