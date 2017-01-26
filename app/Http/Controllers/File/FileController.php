<?php

namespace App\Http\Controllers\File;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Http\Request;
use MicrosoftAzure\Storage\Blob\Models\BlockList;
use MicrosoftAzure\Storage\Common\ServiceException;
use WindowsAzure\Common\ServicesBuilder;

class FileController extends Controller
{
//    public function index()
//    {
//        return view('file/upload');
//    }

    public function storageFileUpload(Request $request)
    {
        $file = $request->file('photo');

        // Azure Blob Storage 연결에 필요한 문자열
        $connectionString = 'DefaultEndpointsProtocol=https;AccountName=' . env('ACCOUNTNAME') . ';AccountKey=' . env('ACCOUNTKEY');

        // Create blob REST proxy.
        // Azure Blob Storage에 연결
        $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($connectionString);

        try {
            // Upload blob
            // 파일 이름을 시간으로 변경
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $blockId = 1;
            $blocklist = new BlockList();
            $blocklist->addLatestEntry(md5($blockId));
            $content = file_get_contents($file->getRealPath());
            // 이미지 파일 업로드를 위한 블럭을 만들고
            $blobRestProxy->createBlobBlock('images', $filename, md5($blockId), $content);
            // 이미지를 업로드
            $blobRestProxy->commitBlobBlocks("images", $filename, $blocklist->getEntries());
        } catch (ServiceException $e) {
            // Handle exception based on error codes and messages.
            // Error codes and messages are here:
            // http://msdn.microsoft.com/library/azure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code . ": " . $error_message . "<br />";
        }
    }

    static function getImageUrl()
    {
        // Azure Blob Storage 연결에 필요한 문자열
        $connectionString = 'DefaultEndpointsProtocol=https;AccountName=' . env('ACCOUNTNAME') . ';AccountKey=' . env('ACCOUNTKEY');

        // Create blob REST proxy.
        // Azure Blob Storage에 연결
        $blobRestProxy = ServicesBuilder::getInstance()->createBlobService($connectionString);

        try {
            // List blobs.
            // 서버에 저장되어 있는 파일들의 목록을 가져온다
            $blob_list = $blobRestProxy->listBlobs('images');
            $blobs = $blob_list->getBlobs();

            // 키 값을 사용하여 정렬
            ksort($blobs);

            // URL을 반환
            return $blobs[count($blobs) - 2]->getUrl();
        } catch (ServiceException $e) {
            // Handle exception based on error codes and messages.
            // Error codes and messages are here:
            // http://msdn.microsoft.com/library/azure/dd179439.aspx
            $code = $e->getCode();
            $error_message = $e->getMessage();
            echo $code . ": " . $error_message . "<br />";
        }
    }
}
