<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Admin\Services\Collection;

use Admin\Services\BaseService;
use Common\Models\Collection\Collection;
use Common\Utils\Data\ArrayHelper;

class CollectionConfigServer extends BaseService
{

    /**
     * @return array
     */
    public function getOptionDialProgress($type = 'all')
    {
        $progress = Collection::PROGRESS_ALL;
        $dial = ts(Collection::DIAL_ALL, 'collection');
        if ($type == 'noself') {
            $progress = Collection::PROGRESS_NOSELF;
            $dial = ts(Collection::DIAL_NOSELF, 'collection');
        } elseif ($type == 'self') {
            $progress = Collection::PROGRESS_SELF;
            $dial = ts(Collection::DIAL_SELF, 'collection');
        }
        $option = [];
        foreach ($progress as $key => $value) {
            $option[] = [
                'value' => $key,
                'label' => array_get($dial, $key),
                'children' => ArrayHelper::arrToOption(ts($value, 'collection')),
            ];
        }
        return $option;
    }

    /**
     * @return array
     */
    public function getOptionDial()
    {
        return ArrayHelper::arrToOption(ts(Collection::DIAL_ALL, 'collection'));
    }

    /**
     * @param string $dial
     * @return array
     */
    public function getOptionProgress($dial)
    {
        if (!$dial) {
            $dial = Collection::DIAL_NORMAL_CONTACT;
        }
        return array_get(ts(Collection::PROGRESS_ALL, 'collection'), $dial, []);
    }

}
