<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/**
 * check_postion 控制器
 */
class Check_postion extends Admin_Controller {

    public function __construct() {
        parent::__construct();

        $this->load->model('Check_postion_model');
        $this->load->model('Hospital_model');
    }
                
    public function index() {
        //$data = array();
        $param = array();
        $inParams = array();
        $likeParam = array();

        $this->data['statuss'] = $this->Check_postion_model->getStatus();

        //自动获取get参数
        $urlGet = '';
        //搜索筛选
        $this->data['keyword'] = $this->input->get('search', TRUE);
        if($this->data['keyword']) {
            $likeParam['check_postion'] = $this->data['keyword'];
            $likeParam['remark'] = $this->data['keyword'];
            $urlGet = "?keyword=".$this->data['keyword'];
        }

        //排序
        $orderBy = $this->input->get('orderBy', TRUE);
        $orderBySQL = 'id DESC';
        if ($orderBy == 'idASC') {
            $orderBySQL = 'id ASC';
        }
        $this->data['orderBy'] = $orderBy;

        //分页参数
        $pageUrl = B_URL.'check_postion/index';  //分页链接


        //获取数据
        $result = $this->Check_postion_model->getResult($param, $this->per_page, $this->offset, $orderBySQL, $inParams, $likeParam);

        //生成分页链接
        $total = $this->Check_postion_model->count($param, $inParams, $likeParam);

        $this->initPage($pageUrl.$urlGet, $total, $this->per_page);


        //获取数据
        $hospitals = $this->Hospital_model->getAll();
        $this->data['hospitals'] = $hospitals;
        $this->data['result'] = $result;

        //加载模板
        $this->template->admin_load('admin/check_postion/index',$this->data); //$this->data
    }

    public function save() {
        $data = array();
        $data['statuss'] = $this->Check_postion_model->getStatus();

        if ($this->input->method() == "post") {
            $this->form_validation->set_rules('id', 'id', 'trim');
            $this->form_validation->set_rules('check_postion', 'check_postion', 'trim');
            $this->form_validation->set_rules('hospital_id', 'hospital_id', 'trim');
            $this->form_validation->set_rules('money', 'money', 'trim');
            $this->form_validation->set_rules('remark', 'remark', 'trim');
            $this->form_validation->set_rules('create_time', 'create_time', 'trim');
            $this->form_validation->set_rules('update_time', 'update_time', 'trim');
            $this->form_validation->set_rules('status', 'status', 'trim');

            $param = array(
                'id' => $this->input->post('id', TRUE),
                'check_postion' => $this->input->post('check_postion', TRUE),
                'hospital_id' => $this->input->post('hospital_id', TRUE),
                'money' => $this->input->post('money', TRUE),
                'remark' => $this->input->post('remark', TRUE),
                'update_time' => date('Y-m-d H:i:s'),
                'status' => $this->input->post('status', TRUE),

            );
            $success = FALSE;
            $message = '';
            $message_type = 'fail';

            if ($this->form_validation->run() == FALSE) {
                $message = '表单填写有误';
                 //加载模板
                $this->template->admin_load('admin/check_postion/save', $data);
            } else {
                //保存记录
                $save = $this->Check_postion_model->save($param);

                if ($save) {
                    $message = '保存成功';
                    $success = TRUE;
                    $message_type = 'success';
                } else {
                    $message = '保存失败';
                }

                $this->session->set_flashdata('message_type', $message_type);
                $this->session->set_flashdata('message', $message);
                 //返回列表页面
                $form_url = $this->session->userdata('list_page_url');
                if(empty($form_url)){
                    $form_url = "/admin/check_postion/index";
                }
                else{
                    $this->session->unset_userdata('list_page_url');
                }
                redirect($form_url, 'refresh');

            }

        } else {
            //显示记录的表单
            //$id = intval($this->input->get('id'));
            $id = $this->uri->segment(4);
            if ($id) {
                $data['data'] = $this->Check_postion_model->getRow(array('id' => $id));
            }
            //获取数据
            $hospitals = $this->Hospital_model->getAll();
            $data['hospitals'] = $hospitals;
            $this->template->admin_load('admin/check_postion/save', $data);
        }
    }

    public function manage() {

        if ($this->input->method() == "post") {
            $hospital_id = $this->input->post('hospital_id', TRUE);
            $ids = $this->input->post('ids', TRUE);
            $ids = json_decode($ids);

            $datas = array("hospital_id"=> $hospital_id);
            $result = $this->Check_postion_model->updates($ids, $datas);
            if ($result) {
                $message = "操作成功！";
            } else {
                $message = "操作失败！";
            }
            $this->ajaxReturn($ids, 0, $message, true);
            return;
        }else{
            return $this->ajaxReturn(null, -1, "操作失败", true);
        }
    }

    public function del() {

        $id = $this->uri->segment(4);

        if ($this->input->method() == "post") {
            if ($this->Check_postion_model->delete($id)) {
                $this->session->set_flashdata('message_type', 'success');
                $this->session->set_flashdata('message', "删除成功！");
            } else {
                $this->data["message"] = "<div class=\"alert alert-danger alert-dismissable\"><button class=\"close\" data-dismiss=\"alert\">&times;</button>删除时发生错误，请稍后再试！</div>";
            }
        }
        $this->data['id'] = $id;
        $this->load->view('admin/check_postion/modals/del', $this->data);
    }

        //详情
        public function view()
        {
            $id = $this->uri->segment(4);

            //获取数据
            $obj = $this->Check_postion_model->getRow(array("id" => $id));
            if(empty($obj)){
                redirect('admin/check_postion/index', 'refresh');
            }        $this->data['statuss'] = $this->Check_postion_model->getStatus();

            // 传递数据
            $this->data['data']  = $obj;

            //当前列表页面的url
            $form_url = empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'];
            if(strripos($form_url,"admin/check_postion") === FALSE){
                $form_url = "/admin/check_postion/index";
            }
            $this->data['form_url'] = $form_url;
            //加载模板
            $this->template->admin_load('admin/check_postion/view', $this->data);
        }

    public function finance() {

        //搜索筛选
        $this->data['start_date'] = $this->input->get('start_date');
        $this->data['end_date'] = $this->input->get('end_date');
        $keyword = $this->input->get('keyword', TRUE);
        $this->data['keyword'] = $keyword;

        //自动获取get参数
        $urlGet = '';
        $gets = $this->input->get();
        if ($gets) {
            $i = 0;
            foreach ($gets as $getKey => $get) {
                if ($i) {
                    $urlGet .= "&$getKey=$get";
                } else {
                    $urlGet .= "/?$getKey=$get";
                }
                $i++;
            }
        }

        //排序
        $orderBy = $this->input->get('orderBy', TRUE);
        $orderBySQL = 'id DESC';
        if ($orderBy == 'idASC') {
            $orderBySQL = 'id ASC';
        }
        $this->data['orderBy'] = $orderBy;

        //分页参数
        $pageUrl = B_URL.'check_postion/finance';  //分页链接
        $suffix = $urlGet;   //GET参数

        //获取数据

        $result = $this->Hospital_model->getFinance( $this->per_page, $this->offset, $this->data['start_date'], $this->data['end_date']);

        //生成分页链接
        $total = $this->Hospital_model->getFinance($this->per_page, $this->offset, $this->data['start_date'], $this->data['end_date'], true);

        $this->initPage($pageUrl.$suffix, $total, $this->per_page);

        $this->data['result'] = $result;

        //加载模板
        $this->template->admin_load('admin/check_postion/finance',$this->data); //$this->data
    }


    //导出
    public function export() {
        set_time_limit(0);//不设置过期时间

        $message_type = 'error';
        $message = '导出失败';
        if ($this->input->method() == "post") {
            //搜索筛选
            $this->data['keyword'] = $this->input->get('keyword', TRUE);
            $this->data['start_date'] = $this->input->get('start_date', TRUE);
            $this->data['end_date'] = $this->input->get('end_date', TRUE);

            $result = $this->Hospital_model->getFinance(0, 0, $this->data['start_date'], $this->data['end_date'], false, $this->data['keyword']);
            if (!empty($result)) {
                $fields_array = array('hospital_id', 'name', 'cp_id', 'check_position', 'user_id', 'date', 'money');
                return $this->exportExcel($result, "导出数据.xlsx", $fields_array);
            }

        }
        $this->session->set_flashdata('message_type', $message_type);
        $this->session->set_flashdata('message', $message);
        //返回列表页面
        $form_url = $this->session->userdata('list_page_url');
        if (empty($form_url)) {
            $form_url = "/admin/check_postion/finance";
        } else {
            $this->session->unset_userdata('list_page_url');
        }
        redirect($form_url, 'refresh');
    }


    /**
     * @param $list
     * @param $filename
     * @param array $indexKey
     * @throws PHPExcel_Exception
     * @throws PHPExcel_Reader_Exception
     * @throws PHPExcel_Writer_Exception
     * 比如: $indexKey与$list数组对应关系如下:
     *     $indexKey = array('id','username','sex','age');
     *     $list = array(array('id'=>1,'username'=>'YQJ','sex'=>'男','age'=>24));
     */
    function exportExcel($list,$filename,$indexKey=array()){
        $this->load->library('PHPExcel');  //注意路径
        //$PHPExcel = new PHPExcel();            //如果excel文件后缀名为.xls，导入这个类

        //$this->load->library('PHPExcel/Writer/Excel2007');
        require(APPPATH . '/libraries/PHPExcel/Writer/Excel2007.php');
        require(APPPATH . '/libraries/PHPExcel/Reader/PHPExcel_Reader_Excel2007.php');
        require(APPPATH . '/libraries/PHPExcel/Reader/PHPExcel_Reader_Excel5.php');
        require(APPPATH . '/libraries/PHPExcel/Writer/Excel5.php');
        require(APPPATH . 'libraries/PHPExcel/IOFactory.php');

        /* require_once dirname(__FILE__) . '/Lib/Classes/PHPExcel/IOFactory.php';
         require_once dirname(__FILE__) . '/Lib/Classes/PHPExcel.php';
         require_once dirname(__FILE__) . '/Lib/Classes/PHPExcel/Writer/Excel2007.php';*/

        $header_arr = array('A','B','C','D','E','F','G','H');

        //$objPHPExcel = new PHPExcel();                        //初始化PHPExcel(),不使用模板
        //$template = dirname(__FILE__).'/template.xls';          //使用模板
        $template = 'downloads/template.xlsx';          //使用模板
        $objPHPExcel = PHPExcel_IOFactory::load($template);     //加载excel文件,设置模板

        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);  //设置保存版本格式

        //接下来就是写数据到表格里面去
        $objActSheet = $objPHPExcel->getActiveSheet();
        //$objActSheet->setCellValue('A2',  "活动名称：江南极客");
        //$objActSheet->setCellValue('C2',  "导出时间：".date('Y-m-d H:i:s'));
        $i = 2;
        foreach ($list as $ikey => $row) {
            foreach ($indexKey as $key => $value){
                $temp = $row->{$value};
//                if( $value == "total_cost" || $value == "profit" ||  $value == "profit_rate"){
//                    $temp = sprintf ($temp, $i);
//                }
                //这里是设置单元格的内容
                $objActSheet->setCellValue($header_arr[$key].$i,$temp);
            }
            $i++;
        }

        //杂费
//        if($list['za_fee']){
//            $objActSheet->setCellValue('B'.($i+6),"杂费：".$list['za_fee']);
//        }

        // 1.保存至本地Excel表格
        //$objWriter->save($filename.'.xls');

        // 2.接下来当然是下载这个表格了，在浏览器输出就好了
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$filename.'"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }

}
