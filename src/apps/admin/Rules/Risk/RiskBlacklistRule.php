<?php

namespace Admin\Rules\Risk;

use Admin\Models\Upload\Upload;
use Common\Models\Risk\RiskBlacklist;
use Common\Rule\Rule;

class RiskBlacklistRule extends Rule
{
    const SCENARIO_LIST = 'list';
    const SCENARIO_IMPORT = 'import';

    /**
     * @return array
     */
    public function rules()
    {
        return [
            self::SCENARIO_LIST => [
                'keyword' => 'string|in:' . implode(',', array_keys(RiskBlacklist::KEYWORD_ALIAS)),
                'is_global' => 'string|in:' . implode(',', [RiskBlacklist::IS_GLOBAL_YES, RiskBlacklist::IS_GLOBAL_NO]),
                'black_reason' => 'string|in:' . implode(',', RiskBlacklist::TYPE_ALIAS),
            ],
            self::SCENARIO_IMPORT => [
                'file' => 'required|mimes:' . implode(',', Upload::EXTENSION),
            ],
        ];
    }

    /**
     * @return array|mixed
     */
    public function messages()
    {
        return [
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
