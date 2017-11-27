<?php

namespace Apiato\Core\Transformers;


use Apiato\Core\Abstracts\Transformers\Transformer;
use stdClass;


class ComposerTransformer extends Transformer
{

    /**
     * @param stdClass $decodedJson
     * @return array
     */
    public function transform(stdClass $decodedJson)
    {
        $ret = [
            'name' => $decodedJson->name,
            'description' => $decodedJson->name,
        ];
        if (isset($decodedJson->type))
            $ret['type'] = $decodedJson->type;
        if (isset($decodedJson->support))
            $ret['support'] = (array)$decodedJson->support;
        return $ret;
    }
}
