####AliOss上传说明
- 参考
    - [Laravel 的文件系统和云存储功能集成](https://learnku.com/docs/laravel/5.5/filesystem/1319#file-urls)
    - [Aliyun-oss-storage](https://github.com/jacobcyl/Aliyun-oss-storage/tree/2482d751cbc169034aeda1f6df2901ff3e229155)

- `oss`外网节点 `inner-oss`内网节点

- 上传文件
```
// $driver可以用`oss`(使用外网上传),`inner-oss`(使用内网上传),返回oss存储的文件名
$request->file(filename)->store($path,$driver);

// 第二种上传方式
$file = $request->file(filename);
$path = Storage::disk($driver)->put($path, $file);
``` 
- 自定义文件名
```
// $driver可以用`oss`(使用外网上传),`inner-oss`(使用内网上传),`$fileName`(自定义的文件名,记得带上后缀),返回oss存储的文件名
$request->file(filename)->storeAs($path,$fileName, $driver);

// 第二种上传方式
$file = $request->file(filename);
$path = Storage::putFileAs($path, $file, $fileName);

// 第三种上传方式 具体查看 `src/apps/_common/Services/AliOssStorage/Plugins/PutAs.php`方法
Storage::disk('oss')->putAs($path/$filename, $file,$options=[]);

```
- 设置bucket,setBucket是自定义方法具体查看`src/apps/_common/Services/AliOssStorage/Plugins/SetBucket.php`
```
// 默认使用config('filesystems.disks.oss.buckets')数组的第一个bucket.如果需要切换bucket使用下面的方式
Storage::disk($driver)->setBucket($bucket)->url('test/I9eJrF0DR7LkwBSTuZZsWcurBGhEtgk03weOvkS3.jpeg');
```

- 获取url,ossUrl是自定义方法具体查看`src/apps/_common/Services/AliOssStorage/Plugins/GetOssUrl.php`
```
//  使用`ossUrl方法获取域名可以设置图片高度和是否使用CDN.CDN可以在filesystem开启全局CDN
Storage::disk($driver)->ossUrl($fileName,$height,$useDomain);
// 使用`url`方法不能设置参数
Storage::disk($driver)->url($fileName);
```

- oss文件转存到本地,getFile是自定义方法具体查看`src/apps/_common/Services/AliOssStorage/Plugins/GetFile.php`
```
//  使用`getFile`方法可以把oss文件转存到本地,`$object`(oss文件),`localFile`(本地存储位置)
Storage::disk('oss')->getFile($object, $localFile);
// 使用`url`方法不能设置参数
Storage::disk($driver)->url($fileName);
```

