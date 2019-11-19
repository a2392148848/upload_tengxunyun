<?php
namespace app\manage\controller;
require '../extend/lib/vod-sdk-v5/autoload.php';
use think\Controller;
use think\db;
use think\Request;
use Endroid\QrCode\QrCode;
use TencentCloud\Common\Credential;
use TencentCloud\Common\Profile\ClientProfile;
use TencentCloud\Common\Profile\HttpProfile;
use TencentCloud\Common\Exception\TencentCloudSDKException;
use TencentCloud\Vod\V20180717\VodClient;
use TencentCloud\Vod\V20180717\Models\ApplyUploadRequest;
use TencentCloud\Vod\V20180717\Models\CommitUploadRequest;
use Vod\Model\VodUploadRequest;
use Vod\VodUploadClient;
class Vio extends Common{
    //tp5.1上传视频
    public function b(){
        try {
            $file = request()->file('file');
            $info = $file->move( '../public/Uploads');
            if($info){
                $name=$info->getSaveName();
                if($info->getExtension()!="mp4"){
                    $arr=['msg'=>0,'info'=>'文件格式不是mp4'];
                }else{
                    $client = new VodUploadClient(config('secret_id'),config('secret_key'));
                    $req = new VodUploadRequest();
                    $root=config('root');					//服务器路径,不能使用网址路径
                    $req->MediaFilePath = $root.'/public/Uploads/'.$name;					//执行任务流
                    $req->Procedure = "640";
                    try {
                        $rsp = $client->upload("ap-chongqing", $req);
                        $FileId=$rsp->FileId;
                        $url=$rsp->MediaUrl;
                        $data=[
                            'file_id'=>$FileId,
                            'url'=>$url
                        ];
                        if($FileId&&$url){
                            $insert=db('video')->insertGetId($data);
                            if($insert){
                                $arr=['msg'=>1,'info'=>'文件上传成功'];
                            }else{
                                $arr=['msg'=>0,'info'=>'文件上传失败'];
                            }
                        }
                    } catch (Exception $e) {
                        // 处理上传异常
                        echo $e;
                    }
                }
                unlink('Uploads/'.$name);
                echo json_encode($arr);
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }catch(TencentCloudSDKException $e) {
            echo $e;
        }
    }
}