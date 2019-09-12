<?php
namespace app\index\controller;
use think\Controller;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Style\Alignment;
use PhpOffice\PhpPresentation\Reader\PhpPptTree;
use PhpOffice\PhpPresentation\Reader\Pptimg;
use PhpOffice\PhpPresentation\Shape\RichText;
use PhpOffice\PhpPresentation\Shape\RichText\TextElement;
use PhpOffice\PhpPresentation\Shape\MemoryDrawing;
use upload\Uploader;
// use function GuzzleHttp\json_encode;
// header('Access-Control-Allow-Origin: * ');
// header("Access-Control-Allow-Headers:*");// 制定允许其他域名访问
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
//请求头
header('Access-Control-Allow-Headers:*');
// 响应头设置
header('Access-Control-Allow-Credentials:false');

class Ppt extends Controller
{
    protected $htmlOutput;
    public function __construct(PhpPresentation $oPHPPpt)
    {
        parent::__construct();
        $this->oPhpPresentation = $oPHPPpt;
    }
    public function index()
    {
        return $this->fetch('');
    }
    public function uploadFile(){
        /* 上传配置 */
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("../config.json")), true);
        $base64 = "upload";
        switch (htmlspecialchars($_GET['action'])) {
            case 'uploadimage':
                $config = array(
                    "pathFormat" => $CONFIG['imagePathFormat'],
                    "maxSize" => $CONFIG['imageMaxSize'],
                    "allowFiles" => $CONFIG['imageAllowFiles']
                );
                $fieldName = $CONFIG['imageFieldName'];
                break;
            case 'uploadscrawl':
                $config = array(
                    "pathFormat" => $CONFIG['scrawlPathFormat'],
                    "maxSize" => $CONFIG['scrawlMaxSize'],
                    "allowFiles" => $CONFIG['scrawlAllowFiles'],
                    "oriName" => "scrawl.png"
                );
                $fieldName = $CONFIG['scrawlFieldName'];
                $base64 = "base64";
                break;
            case 'uploadvideo':
                $config = array(
                    "pathFormat" => $CONFIG['videoPathFormat'],
                    "maxSize" => $CONFIG['videoMaxSize'],
                    "allowFiles" => $CONFIG['videoAllowFiles']
                );
                $fieldName = $CONFIG['videoFieldName'];
                break;
            case 'uploadfile':
            default:
                $config = array(
                    "pathFormat" => $CONFIG['filePathFormat'],
                    "maxSize" => $CONFIG['fileMaxSize'],
                    "allowFiles" => $CONFIG['fileAllowFiles']
                );
                ///var_dump(input());die;
                $fieldName = $CONFIG['fileFieldName'];
                break;
        }
        /* 生成上传实例对象并完成上传 */
        if(isset($_FILES[$fieldName])){
            $up = new Uploader($fieldName, $config, $base64);

            /**
             * 得到上传文件所对应的各个参数,数组结构
             * array(
             *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
             *     "url" => "",            //返回的地址
             *     "title" => "",          //新文件名
             *     "original" => "",       //原始文件名
             *     "type" => ""            //文件类型
             *     "size" => "",           //文件大小
             * )
             */

            /* 返回数据 */
            return json_encode($up->getFileInfo());
        }
    }
    public function uploads()
    {
        $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("../config.json")), true);
        $action = $_GET['action'];
        
        switch ($action) {
            case 'config':
                $result =  json_encode($CONFIG);
                break;
            /* 上传图片 */
            case 'uploadimage':
            /* 上传涂鸦 */
            case 'uploadscrawl':
            /* 上传视频 */
            case 'uploadvideo':
            /* 上传文件 */
            case 'uploadfile':
                $result = $this->uploadFile();
                break;
        
            /* 列出图片 */
            case 'listimage':
                $result = include("../action_list.php");
                break;
            /* 列出文件 */
            case 'listfile':
                $result = include("../action_list.php");
                break;
        
            /* 抓取远程文件 */
            case 'catchimage':
                $result = include("../action_crawler.php");
                break;
        
            default:
                $result = json_encode(array(
                    'state'=> '请求地址出错'
                ));
                break;
        }
        
        /* 输出结果 */
        if (isset($_GET["callback"])) {
            if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
                echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
            } else {
                echo json_encode(array(
                    'state'=> 'callback参数不合法'
                ));
            }
        } else {
            echo $result;
        }
    }
    public function indexs()
    {
        // // dump($_FILES);
        // if ($_GET['action'] != 'uploadfile') {
        //     exit($this->uploads());
        // } 
        // $CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("../config.json")), true);
        // $config = array(
        //     "pathFormat" => $CONFIG['filePathFormat'],
        //     "maxSize" => $CONFIG['fileMaxSize'],
        //     "allowFiles" => $CONFIG['fileAllowFiles']
        // );
        // $base64 = "upload";
        // include('../Uploader.class.php');
        // $fieldName = $CONFIG['fileFieldName'];
        // $up = new \Uploader($fieldName, $config, $base64);
        // dump($up);
        // //return json(1111111111111);
        //dump(input('post.'));die;
        //dump($_FILES);die;
        // $uploaddir =  ROOT_PATH . 'public/download/';
        // $uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
        // move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile);
        // dump($uploadfile);die;
        // $objPHPPowerPoint = new PhpPresentation();
        // $currentSlide = $objPHPPowerPoint->getActiveSlide();
        // dump($currentSlide);
        //dump(ROOT_PATH);die;
        //$input = input('post.');
        //dump($_FILES);die;
        //$oldname = $input['name'];
        $uploaddir =  ROOT_PATH . 'public/download/';
        $uploadfile = $uploaddir . time() .basename($_FILES['fileArray']['name']);
        move_uploaded_file($_FILES['fileArray']['tmp_name'], $uploadfile);
        // dump($uploadfile);die;
        //$name = ROOT_PATH . 'public/download/'.$oldname;
        $pptReader = IOFactory::createReader('PowerPoint2007');//reader ppt obj
        // $oPHPPresentation = $pptReader->load(ROOT_PATH . 'public/download/test.pptx');
        $oPHPPresentation = $pptReader->load($uploadfile);
        //获取ppt页数
        $c=$oPHPPresentation->getSlideCount();
        //dump($c);die;
        //获取ppt文字
        // $pptstree = new Pptimg($oPHPPresentation);
        // $html = $pptstree->display();
        // dump($html);die;
        //获取ppt图片
        // $arr = [];
        for($i=0;$i<$c;$i++){
            //echo 111;
            $oSlide=$oPHPPresentation->getSlide($i);
            //$oSlide=$oPHPPresentation->getSlide(1);
            //dump($oSlide);
            $oShapeCollection = $oSlide->getShapeCollection();
            // dump($oSlide);
            $str1 = '';
            foreach ($oSlide->getShapeCollection() as $oShape){
                $offsetX =  $oShape->getOffsetX();
                $offsetY =  $oShape->getOffsetY();
                $height  =  $oShape->getHeight();
                $hidth   =  $oShape->getWidth();
                //dump($oShape);
                if($oShape instanceof \PhpOffice\PhpPresentation\Shape\Drawing\Gd){//判断属于图片
                    // echo 111555;
                    ob_start();
                    call_user_func($oShape->getRenderingFunction(), $oShape->getImageResource());
                    $sShapeImgContents = ob_get_contents();
                    ob_end_clean();
                    //dump($sShapeImgContents);
                    $name = $i+1;
                    file_put_contents(ROOT_PATH . 'public\download\\'.$name.'.png', $sShapeImgContents);
                    // echo '< img src="data:'.$oShape->getMimeType().';base64,'.base64_encode($sShapeImgContents).'">';
                    // echo base64_encode($sShapeImgContents);echo '<br/>';
                    //$a = $this->append('<dt>Mime-Type</dt><dd>'.$oShape->getMimeType().'</dd>');
                    //$str1 .= '<dt>Offset X</dt><dd>'.$oShape->getOffsetX().'</dd>';
                    //$str1 .= '<dt>Offset X</dt><dd>'.$oShape->getOffsetY().'</dd>';
                    //$str1 .= '<dt>Offset X</dt><dd>'.$oShape->getHeight().'</dd>';
                    //$str1 .= '<dt>Offset X</dt><dd>'.$oShape->getWidth().'</dd>';
                    $str1 .= '<dd><img src="data:'.$oShape->getMimeType().';base64,'.base64_encode($sShapeImgContents).'"></dd>';
                    //$b = $this->append('<dt>Image</dt><dd>< img src="data:'.$oShape->getMimeType().';base64,'.base64_encode($sShapeImgContents).'"></dd>');
                    $arr[$i][] = $str1;
                    // $imageResource  =  $oShape->getImageResource();
                    //var_dump($imageResource);
                    //echo "<br>";
                    continue;
                }
                foreach ($oShape->getParagraphs() as $oParagraph) {
                    //$oParagraph->getBulletStyle()->getBulletColor()->getARGB();
                    //dump($oParagraph);die;
                    // $hashCode = $oParagraph->getHashCode();
                    // echo $oParagraph->TextElement();
                    $str = '';
                    //dump($oParagraph->getRichTextElements());die;
                    foreach ($oParagraph->getRichTextElements() as $oRichText) {
                        $getName = $oRichText->getFont()->getName();
                        $colorold =$oRichText->getFont()->getColor()->getARGB();
                        //dump($colorold);
                        $color = "#".substr($colorold,"2");
                        //dump($color);die;
                        $size = $oRichText->getFont()->getSize()."px";
                        //dump($color);die;
                        $test = $oRichText->getText();
                        // $str .= "<html><dd style=face:$getName,size:$size,color:$color>".."</dd></html>";
                        $str .=  '<html><dd style="font-family:' . $getName . ';font-size:' . $size . ';color:' . $color . '">'. $test .'</dd></html>';
                        //$str .=  '<html><dd style="font-family:Microsoft Yahei' . ';font-size:' . $size . ';color:' . $color . '">'. $test .'</dd></html>';
                        //$str .= '<dt>Font Name</dt><dd>'.$oRichText->getFont()->getName().'</dd>';
                        //$str .= '<dt>Font Size</dt><dd>'.$oRichText->getFont()->getSize().'</dd>';
                        //$str .= '<dt>Font Color</dt><dd>#'.$oRichText->getFont()->getColor()->getARGB().'</dd>';
                    }
                    //dump($str);
                    $arr[$i][] = $str;
                }
            }
        }
        // dump($arr);die;
        // return $arr;
        return json_encode($arr,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }
    protected function _returnData($status,$message,$data=array()){

        $return = array();
        $return['status'] = $status;
        $return['message'] = $message;
        $return['data'] = $data;

        exit(json_encode($return,JSON_UNESCAPED_UNICODE));


    }
    protected function append($sHTML)
    {
       return $this->htmlOutput = $sHTML;
        
    }
    public function action_upload()
    {
        //include "../Uploader.class.php";

        /* 上传配置 */
        $base64 = "upload";
        switch (htmlspecialchars($_GET['action'])) {
            case 'uploadimage':
                $config = array(
                    "pathFormat" => $CONFIG['imagePathFormat'],
                    "maxSize" => $CONFIG['imageMaxSize'],
                    "allowFiles" => $CONFIG['imageAllowFiles']
                );
                $fieldName = $CONFIG['imageFieldName'];
                break;
            case 'uploadscrawl':
                $config = array(
                    "pathFormat" => $CONFIG['scrawlPathFormat'],
                    "maxSize" => $CONFIG['scrawlMaxSize'],
                    "allowFiles" => $CONFIG['scrawlAllowFiles'],
                    "oriName" => "scrawl.png"
                );
                $fieldName = $CONFIG['scrawlFieldName'];
                $base64 = "base64";
                break;
            case 'uploadvideo':
                $config = array(
                    "pathFormat" => $CONFIG['videoPathFormat'],
                    "maxSize" => $CONFIG['videoMaxSize'],
                    "allowFiles" => $CONFIG['videoAllowFiles']
                );
                $fieldName = $CONFIG['videoFieldName'];
                break;
            case 'uploadfile':
            default:
                $config = array(
                    "pathFormat" => $CONFIG['filePathFormat'],
                    "maxSize" => $CONFIG['fileMaxSize'],
                    "allowFiles" => $CONFIG['fileAllowFiles']
                );
                $fieldName = $CONFIG['fileFieldName'];
                break;
        }

        /* 生成上传实例对象并完成上传 */
        $up = new Uploader($fieldName, $config, $base64);

        /**
         * 得到上传文件所对应的各个参数,数组结构
         * array(
         *     "state" => "",          //上传状态，上传成功时必须返回"SUCCESS"
         *     "url" => "",            //返回的地址
         *     "title" => "",          //新文件名
         *     "original" => "",       //原始文件名
         *     "type" => ""            //文件类型
         *     "size" => "",           //文件大小
         * )
         */

        /* 返回数据 */
        return json_encode($up->getFileInfo());
    }
}