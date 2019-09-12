<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Loader;
class Phpexcel extends Controller
{
    //excel 获取内容
    public function excelcontent()
    {
        Vendor('phpoffice.Classes.PHPExcel.IOFactory');
        $objPHPExcel = \PHPExcel_IOFactory::load(ROOT_PATH . 'public/know/know.xlsx');
        $sheet=$objPHPExcel->getSheet();
        //获取sheet表格数目

        $sheetCount = $objPHPExcel->getSheetCount();

        //默认选中sheet0表

        $sheetSelected = 0;$objPHPExcel->setActiveSheetIndex($sheetSelected);

        //获取表格行数

        $rowCount = $objPHPExcel->getActiveSheet()->getHighestRow();

        //获取表格列数

        $columnCount = $objPHPExcel->getActiveSheet()->getHighestColumn();

        // echo "<div>Sheet Count : ".$sheetCount."　　行数： ".$rowCount."　　列数：".$columnCount."</div>";

        $dataArr = array();

        /* 循环读取每个单元格的数据 */

        //行数循环

        for ($row = 1; $row <= $rowCount; $row++){

            //列数循环 , 列数是以A列开始

            $str = '';
            for ($column = 'A'; $column <= $columnCount; $column++) {

                $dataArr[$column] = $objPHPExcel->getActiveSheet()->getCell($column.$row)->getValue();
                // dump($dataArr);
                //$str[] = array_values($dataArr);

                //echo $column.$row.":".$objPHPExcel->getActiveSheet()->getCell($column.$row)->getValue()."<br />";

                //$arr[$column.$row] = $objPHPExcel->getActiveSheet()->getCell($column.$row)->getValue();

            }
            $arr[] = $dataArr;
            $dataArr = NULL;
        }
        $t = '';
        foreach($arr as $key=>$v){
            $v = join(",",$v); // 可以用implode将一维数组转换为用逗号连接的字符串，join是别名
            $temp[] = $v;
            $t .= $temp[$key].",";
        }
        $t = substr($t, 0, -1);
        foreach ($sheet->getDrawingCollection() as $drawing) {
            list($startColumn,$startRow)= \PHPExcel_Cell::coordinateFromString($drawing->getCoordinates());//获取图片所在行和列
            $string = $drawing->getCoordinates();
            if ($drawing instanceof \PHPExcel_Worksheet_Drawing) {
                $imagePath = $drawing->getPath();
//                dump($imagePath);die;
                $imagePath = substr($imagePath, 6);
                //dump($imagePath);
                $imagePathSplitted = explode('#', $imagePath);
                //dump($imagePathSplitted);
                $imageZip = new \ZipArchive();
                $imageZip->open($imagePathSplitted[0]);
                $imageContents = $imageZip->getFromName($imagePathSplitted[1]); // 这里得到图片的数据流
//                dump($imageContents);die;
                $imageZip->close();
                unset($imageZip);
//                dump($imagePathSplitted);die;
                $expanded = '.' . explode('.', $imagePathSplitted[1])[1]; // 获取扩展名
                //dump($expanded);
                $public  = 'public';
                $imgs = "/know/images/" . uniqid() . mt_rand(10000, 99999) . $expanded;
                $img = $public.$imgs;
                file_put_contents(ROOT_PATH . $img, $imageContents);
                $cell = $sheet->getCell($string);
                $cell->setValue($img);
            }
            $startColumn = $this->ABC2decimal($startColumn);
            $arr[$startRow-1][$startColumn]=ROOT_PATH . 'public'.$imgs;
        }
        foreach ($arr as $key => $value){
            if($key == 0){
                unset($arr[$key]);
            }
        }
        dump($arr);die;
        $lists = '';
        foreach($arr as $key => $value){
            if($value['A'] != NULL){
                $lists .= $value['A'].";";
            }
        }
        $count = count($arr);
        foreach ($arr[1] as $key => $value){
            if($key != 'A'){
                $content_arr = array();
                for ($i = 1 ;$i<=$count ;$i++){
                    if(!empty($arr[$i]['A'])){
                        $content_arr[] = $arr[$i][$key];
                    }
                }
                $content = implode(';',$content_arr);
//                var_dump($content);
                $field = [
                    "typeid"=>1,
                    "userid"=>1,
                    "update_userid"=>1,
                    "type_role"=>1,
                    //"channel"=>1,
                    "type"=>1,
                    "attribute"=>1,
                    "endtime"=>"2019-08-02",
                    "title"=>$arr[1][$key],
                    "cabstract"=>"内容摘要",
                    "img"=>"测试",
                    "content"=>$content,
                    "createtime"=>date('Y-m-d H:i:s',time()),
                    "click_num"=>0,
                    "col_num"=>0,
                    "down_num"=>0,
                    "toptime"=>"2019-02-06",
                    "status"=>1,
                    "deletetime"=>"2019-02-02",
                    "history_id"=>"1",
                    "good_num"=>1,
                    "bad_num"=>1,
                    "list"=>$lists,
                ];
                //dump($field);
                $data=Db::table('know_ledge')->insert($field);
            }
        }
    }
    //文字图片一起   一个wexcel一个表
    public function imgexecl()
    {
        Vendor('phpoffice.Classes.PHPExcel.IOFactory');
        $objReader = \PHPExcel_IOFactory::load(ROOT_PATH . 'public/know/know.xlsx');  //载入文件
        $sheet=$objReader->getSheet();//选择第几个表，如下面图片，默认有三个表
        $arr = $sheet->toArray();//把表格的数据转换为数组，注意：这里转换是以行号为数组的外层下标，列号会转成数字为数组内层下标，坐标对应的值只会取字符串保留在这里，图片或链接不会出现在这里。
		//dump($arr);die;
        foreach ($sheet->getDrawingCollection() as $drawing) {
            list($startColumn,$startRow)= \PHPExcel_Cell::coordinateFromString($drawing->getCoordinates());//获取图片所在行和列
            $string = $drawing->getCoordinates();
            if ($drawing instanceof \PHPExcel_Worksheet_Drawing) {
                $imagePath = $drawing->getPath();
//                dump($imagePath);die;
                $imagePath = substr($imagePath, 6);
                //dump($imagePath);
                $imagePathSplitted = explode('#', $imagePath);
                //dump($imagePathSplitted);
                $imageZip = new \ZipArchive();
                $imageZip->open($imagePathSplitted[0]);
                $imageContents = $imageZip->getFromName($imagePathSplitted[1]); // 这里得到图片的数据流
//                dump($imageContents);die;
                $imageZip->close();
                unset($imageZip);
//                dump($imagePathSplitted);die;
                $expanded = '.' . explode('.', $imagePathSplitted[1])[1]; // 获取扩展名
                //dump($expanded);
                $public  = 'public';
                $imgs = "/download/images/" . uniqid() . mt_rand(10000, 99999) . $expanded;
                $img = $public.$imgs;
                file_put_contents(ROOT_PATH . $img, $imageContents);
                $cell = $sheet->getCell($string);
                $cell->setValue($img);
            }
            $startColumn = $this->ABC2decimal($startColumn);
            $arr[$startRow-1][$startColumn]=ROOT_PATH . 'public'.$imgs;
        }
        //$arr 是返回给前端的
//        dump($arr);die;
        $t = '';
        foreach($arr as $key=>$v){
            $v = join(",",$v); // 可以用implode将一维数组转换为用逗号连接的字符串，join是别名
            $temp[] = $v;
            $t .= $temp[$key].",";
        }
        $t = substr($t, 0, -2);
        //$t 是存储到es
//        dump($t);
        $client = ClientBuilder::create()->setHosts(['192.168.30.97:9200'])->build();
        $params = [
            'index' => 'test9',
            'type' => 'test9',
            "body" =>[
                "message"=>$t
            ]
        ];
        $response = $client->index($params);

    }
    public function ABC2decimal($abc)
    {
        $ten = 0;
        $len = strlen($abc);
        for($i=1;$i<=$len;$i++){
            $char = substr($abc,0-$i,1);//反向获取单个字符

            $int = ord($char);
            $ten += ($int-65)*pow(26,$i-1);
        }
        return $ten;
    }
    public function tesy()
    {
        Vendor('phpoffice.Classes.PHPExcel.IOFactory');
        Vendor('phpoffice.Classes.PHPExcel.Cell');
        $PHPExcel = \PHPExcel_IOFactory::load(ROOT_PATH . 'public/know/know.xlsx');  //载入文件
        //$sheet=$objPHPExcel->getSheet();//选择第几个表，如下面图片，默认有三个表
        $currentSheet = $PHPExcel->getSheet(0);//读取第一个工作表
        $allRow = $currentSheet->getHighestRow();//取得一共有多少行
        //这两段很重要
        $allColumn = $currentSheet->getHighestColumn();//取得最大的列号
        $allColumn = \PHPExcel_Cell::columnIndexFromString($allColumn);//将列数转换为数字 列数大于Z的必须转  A->1  AA->27
        $arr = [];
        //从第一行开始读 第一行为标题
        for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
            //从第A列开始输出
            if($allColumn<26){
                $allColumn = 26;
            }elseif ($allColumn > 27 && $allColumn<720){
                $allColumn = 720;
            }else{
                $allColumn = 1080;
            }
            for ($currentColumn = 0; $currentColumn < $allColumn; $currentColumn++) {

                //plan 1
                $strColumn  = \PHPExcel_Cell::stringFromColumnIndex($currentColumn);//将数字转换为字母  0->A  26->AA
                $val = $currentSheet->getCell($strColumn.$currentRow)->getValue();
                //plan 2
                //$val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                //如果输出汉字有乱码，则需将输出内容用iconv函数进行编码转换，如下将gb2312编码转为utf-8编码输出 $arr[$currentRow][]=  iconv('utf-8','gb2312', $val)."＼t";
                //将每列内容读取到数组中
                $arr[$currentRow][] = trim($val);
//                if($arr[$currentRow][$currentColumn] == ""){
//                    break;
//                }
            }
        }
        //dump($arr);die;
        foreach ($currentSheet->getDrawingCollection() as $drawing) {
            list($startColumn,$startRow)= \PHPExcel_Cell::coordinateFromString($drawing->getCoordinates());//获取图片所在行和列
            $string = $drawing->getCoordinates();
            if ($drawing instanceof \PHPExcel_Worksheet_Drawing) {
                $imagePath = $drawing->getPath();
//                dump($imagePath);die;
                $imagePath = substr($imagePath, 6);
                //dump($imagePath);
                $imagePathSplitted = explode('#', $imagePath);
                //dump($imagePathSplitted);
                $imageZip = new \ZipArchive();
                $imageZip->open($imagePathSplitted[0]);
                $imageContents = $imageZip->getFromName($imagePathSplitted[1]); // 这里得到图片的数据流
//                dump($imageContents);die;
                $imageZip->close();
                unset($imageZip);
//                dump($imagePathSplitted);die;
                $expanded = '.' . explode('.', $imagePathSplitted[1])[1]; // 获取扩展名
                //dump($expanded);
                $public  = 'public';
                $imgs = "/know/images/" . uniqid() . mt_rand(10000, 99999) . $expanded;
                $img = $public.$imgs;
                file_put_contents(ROOT_PATH . $img, $imageContents);
                $cell = $currentSheet->getCell($string);
                $cell->setValue($img);
            }
            $startColumn = $this->ABC2decimal($startColumn);
            $arr[$startRow-1][$startColumn]=ROOT_PATH . 'public'.$imgs;
        }
        foreach ($arr as $key => $value){
            if($key == 1){
                unset($arr[$key]);
            }
        }

        //只是目录
        $lists = '';
        foreach($arr as $key => $value){
            if($value[2] != NULL){
                $lists .= $value['0']."&";
            }
        }
        $count = count($arr);
        foreach ($arr[2] as $key => $value){
            if(empty($value)) break;
            if($key != 0){
                $content_arr = array();
                for ($i = 2 ;$i<=$count ;$i++){
                    //dump($arr[$i]);
                    if(!empty($arr[$i][0])){
                        $content_arr[] = $arr[$i][$key];
                    }
                }
                $content = implode('&',$content_arr);
                $field = [
                    "typeid"=>1,
                    "userid"=>1,
                    "update_userid"=>1,
                    "type_role"=>1,
                    //"channel"=>1,
                    "type"=>1,
                    "attribute"=>1,
                    "endtime"=>"",
                    "title"=>$arr[2][$key],
                    "cabstract"=>"",
                    "img"=>"",
                    "content"=>$content,
                    "createtime"=>date('Y-m-d H:i:s',time()),
                    "click_num"=>0,
                    "col_num"=>0,
                    "down_num"=>0,
                    "toptime"=>"",
                    "status"=>2,
                    "deletetime"=>"",
                    "history_id"=>"",
                    "good_num"=>0,
                    "bad_num"=>0,
                    "list"=>$lists,
                ];
                dump($field);
                $data=Db::table('know_ledge')->insert($field);
            }
        }
    }
}