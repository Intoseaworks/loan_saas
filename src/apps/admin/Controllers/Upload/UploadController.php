<?php

namespace Admin\Controllers\Upload;

use Admin\Rules\Upload\UploadRule;
use Admin\Services\Upload\UploadServer;
use Common\Response\ApiBaseController;
use Common\Utils\LoginHelper;

/**
 * 上传文件
 * Class UploadController
 * @package App\Http\Api\Controllers\Upload
 * @author ChangHai Zhan
 */
class UploadController extends ApiBaseController
{
    /**
     * @param UploadRule $rule
     * @return array
     */
    public function create(UploadRule $rule)
    {
        //数据验证
        if (!$rule->validate(UploadRule::SCENARIO_CREATE, $this->request->all())) {
            return $this->resultFail($rule->getError());
        }
        if (!$attributes = UploadServer::moveFile($this->request->file('file'))) {
            return $this->resultFail('上传文件保存失败');
        }
        $attributes['type'] = $this->request->input('type');
        if (!$model = UploadServer::create($attributes, LoginHelper::getAdminId())) {
            return $this->resultFail('上传文件保存记录失败');
        }
        return $this->resultSuccess($model->getText());
    }

    public function downloadByType(UploadRule $rule)
    {
        $request = $this->request;
        // 数据验证
        if (!$rule->validate($rule::SCENARIO_DOWNLOAD_BY_TYPE, $request->all())) {
            return $this->resultFail($rule->getError());
        }

        $sourceId = $request->input('source_id');
        $type = $request->input('type');
        $paths = UploadServer::getPathsBySourceIdAndType($sourceId, $type)->toArray();

        if (empty($paths)) {
            return $this->resultFail('对应文件不存在');
        }

        $tmpFileName = UploadServer::setDownloadTmpFileName($paths);
        UploadServer::resetDownloadTmp();

        // 报单资料文件 重命名
        $fileName = UploadServer::getFileName($sourceId, $type);

        if (!file_exists($tmpFileName)) {
            if (!$downloadFile = UploadServer::downloadFiles($paths, $tmpFileName)) {
                return $this->resultFail('下载失败');
            }
        }
        // 打包预请求
        if ($request->has('auth')) {
            return $this->resultSuccess();
        }

        return UploadServer::download($tmpFileName, false, $fileName);
    }
}
