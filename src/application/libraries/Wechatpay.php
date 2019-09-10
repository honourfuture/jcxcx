<?php
class WechatPay {
    const TRADETYPE_JSAPI = 'JSAPI',TRADETYPE_NATIVE = 'NATIVE',TRADETYPE_APP = 'APP';
    const URL_UNIFIEDORDER = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    const URL_ORDERQUERY = "https://api.mch.weixin.qq.com/pay/orderquery";
    const URL_CLOSEORDER = 'https://api.mch.weixin.qq.com/pay/closeorder';
    const URL_REFUND = 'https://api.mch.weixin.qq.com/secapi/pay/refund';
    const URL_REFUNDQUERY = 'https://api.mch.weixin.qq.com/pay/refundquery';
    const URL_DOWNLOADBILL = 'https://api.mch.weixin.qq.com/pay/downloadbill';
    const URL_REPORT = 'https://api.mch.weixin.qq.com/payitil/report';
    const URL_SHORTURL = 'https://api.mch.weixin.qq.com/tools/shorturl';
    const URL_MICROPAY = 'https://api.mch.weixin.qq.com/pay/micropay';
    /**
     * ������Ϣ
     */
    public $error = null;
    /**
     * ������ϢXML
     */
    public $errorXML = null;
    /**
     * ΢��֧����������
     * appid        �����˺�appid
     * mch_id       �̻���
     * apikey       ����key
     * appsecret    ���ں�appsecret
     * sslcertPath  ֤��·��(apiclient_cert.pem)
     * sslkeyPath   ��Կ·��(apiclient_key.pem)
     */
    private $_config;
    /**
     * @param $config ΢��֧����������
     */
    public function __construct($config) {
        $this->_config = $config;
    }
    /**
     * JSAPI��ȡprepay_id
     * @param $body
     * @param $out_trade_no
     * @param $total_fee
     * @param $notify_url
     * @param $openid
     * @return null
     */
    public function getPrepayId($body,$out_trade_no,$total_fee,$notify_url,$openid) {
        $data = array();
        $data["nonce_str"]    = $this->get_nonce_string();
        $data["body"]         = $body;
        $data["out_trade_no"] = $out_trade_no;
        $data["total_fee"]    = $total_fee;
        $data["spbill_create_ip"] = $_SERVER["REMOTE_ADDR"];
        $data["notify_url"]   = $notify_url;
        $data["trade_type"]   = self::TRADETYPE_JSAPI;
        $data["openid"]   = $openid;
        $result = $this->unifiedOrder($data);
        if ($result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            return $result["prepay_id"];
        } else {
            $this->error = $result["return_code"] == "SUCCESS" ? $result["err_code_des"] : $result["return_msg"];
            $this->errorXML = $this->array2xml($result);
            return null;
        }
    }
    private function get_nonce_string() {
        return substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"),0,32);
    }
    /**
     * ͳһ�µ��ӿ�
     */
    public function unifiedOrder($params) {
        $data = array();
        $data["appid"] = $this->_config["appid"];
        $data["mch_id"] = $this->_config["mch_id"];
        $data["device_info"] = (isset($params['device_info'])&&trim($params['device_info'])!='')?$params['device_info']:null;
        $data["nonce_str"] = $this->get_nonce_string();
        $data["body"] = $params['body'];
        $data["detail"] = isset($params['detail'])?$params['detail']:null;//optional
        $data["attach"] = isset($params['attach'])?$params['attach']:null;//optional
        $data["out_trade_no"] = isset($params['out_trade_no'])?$params['out_trade_no']:null;
        $data["fee_type"] = isset($params['fee_type'])?$params['fee_type']:'CNY';
        $data["total_fee"]    = $params['total_fee'];
        $data["spbill_create_ip"] = $params['spbill_create_ip'];
        $data["time_start"] = isset($params['time_start'])?$params['time_start']:null;//optional
        $data["time_expire"] = isset($params['time_expire'])?$params['time_expire']:null;//optional
        $data["goods_tag"] = isset($params['goods_tag'])?$params['goods_tag']:null;
        $data["notify_url"] = $params['notify_url'];
        $data["trade_type"] = $params['trade_type'];
        $data["product_id"] = isset($params['product_id'])?$params['product_id']:null;//required when trade_type = NATIVE
        $data["openid"] = isset($params['openid'])?$params['openid']:null;//required when trade_type = JSAPI
        $result = $this->post(self::URL_UNIFIEDORDER, $data);
        return $result;
    }
    private function post($url, $data,$cert = false) {
        $data["sign"] = $this->sign($data);
        $xml = $this->array2xml($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);
        if($cert == true){
            //ʹ��֤�飺cert �� key �ֱ���������.pem�ļ�
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLCERT, $this->_config['sslcertPath']);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($ch,CURLOPT_SSLKEY, $this->_config['sslkeyPath']);
        }
        $content = curl_exec($ch);
        $array = $this->xml2array($content);
        return $array;
    }
    /**
     * ����ǩ��
     * @param $data
     * @return string
     */
    private function sign($data) {
        ksort($data);
        $string1 = "";
        foreach ($data as $k => $v) {
            if ($v && trim($v)!='') {
                $string1 .= "$k=$v&";
            }
        }
        $stringSignTemp = $string1 . "key=" . $this->_config["apikey"];
        $sign = strtoupper(md5($stringSignTemp));
        return $sign;
    }
    private function array2xml($array) {
        $xml = "<xml>" . PHP_EOL;
        foreach ($array as $k => $v) {
            if($v && trim($v)!='')
                $xml .= "<$k><![CDATA[$v]]></$k>" . PHP_EOL;
        }
        $xml .= "</xml>";
        return $xml;
    }
    private function xml2array($xml) {
        $array = array();
        $tmp = null;
        try{
            $tmp = (array) simplexml_load_string($xml);
        }catch(Exception $e){}
        if($tmp && is_array($tmp)){
            foreach ( $tmp as $k => $v) {
                $array[$k] = (string) $v;
            }
        }
        return $array;
    }
    /**
     * ɨ��֧��(ģʽ��)��ȡ֧����ά��
     * @param $body
     * @param $out_trade_no
     * @param $total_fee
     * @param $notify_url
     * @param $product_id
     * @return null
     */
    public function getCodeUrl($body,$out_trade_no,$total_fee,$notify_url,$product_id){
        $data = array();
        $data["nonce_str"]    = $this->get_nonce_string();
        $data["body"]         = $body;
        $data["out_trade_no"] = $out_trade_no;
        $data["total_fee"]    = $total_fee;
        $data["spbill_create_ip"] = $_SERVER["SERVER_ADDR"];
        $data["notify_url"]   = $notify_url;
        $data["trade_type"]   = self::TRADETYPE_NATIVE;
        $data["product_id"]   = $product_id;
        $result = $this->unifiedOrder($data);
        if ($result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
            return $result["code_url"];
        } else {
            $this->error = $result["return_code"] == "SUCCESS" ? $result["err_code_des"] : $result["return_msg"];
            return null;
        }
    }
    /**
     * ��ѯ����
     * @param $transaction_id
     * @param $out_trade_no
     * @return array
     */
    public function orderQuery($transaction_id,$out_trade_no){
        $data = array();
        $data["appid"] = $this->_config["appid"];
        $data["mch_id"] = $this->_config["mch_id"];
        $data["transaction_id"] = $transaction_id;
        $data["out_trade_no"] = $out_trade_no;
        $data["nonce_str"] = $this->get_nonce_string();
        $result = $this->post(self::URL_ORDERQUERY, $data);
        return $result;
    }
    /**
     * �رն���
     * @param $out_trade_no
     * @return array
     */
    public function closeOrder($out_trade_no){
        $data = array();
        $data["appid"] = $this->_config["appid"];
        $data["mch_id"] = $this->_config["mch_id"];
        $data["out_trade_no"] = $out_trade_no;
        $data["nonce_str"] = $this->get_nonce_string();
        $result = $this->post(self::URL_CLOSEORDER, $data);
        return $result;
    }
    /**
     * �����˿� - ʹ���̻�������
     * @param $out_trade_no �̻�������
     * @param $out_refund_no �˿��
     * @param $total_fee �ܽ���λ���֣�
     * @param $refund_fee �˿����λ���֣�
     * @param $op_user_id ����Ա�˺�
     * @return array
     */
    public function refund($out_trade_no,$out_refund_no,$total_fee,$refund_fee,$op_user_id){
        $data = array();
        $data["appid"] = $this->_config["appid"];
        $data["mch_id"] = $this->_config["mch_id"];
        $data["nonce_str"] = $this->get_nonce_string();
        $data["out_trade_no"] = $out_trade_no;
        $data["out_refund_no"] = $out_refund_no;
        $data["total_fee"] = $total_fee;
        $data["refund_fee"] = $refund_fee;
        $data["op_user_id"] = $op_user_id;
        $result = $this->post(self::URL_REFUND, $data,true);
        return $result;
    }
    /**
     * �����˿� - ʹ��΢�Ŷ�����
     * @param $out_trade_no �̻�������
     * @param $out_refund_no �˿��
     * @param $total_fee �ܽ���λ���֣�
     * @param $refund_fee �˿����λ���֣�
     * @param $op_user_id ����Ա�˺�
     * @return array
     */
    public function refundByTransId($transaction_id,$out_refund_no,$total_fee,$refund_fee,$op_user_id){
        $data = array();
        $data["appid"] = $this->_config["appid"];
        $data["mch_id"] = $this->_config["mch_id"];
        $data["nonce_str"] = $this->get_nonce_string();
        $data["transaction_id"] = $transaction_id;
        $data["out_refund_no"] = $out_refund_no;
        $data["total_fee"] = $total_fee;
        $data["refund_fee"] = $refund_fee;
        $data["op_user_id"] = $op_user_id;
        $result = $this->post(self::URL_REFUND, $data,true);
        return $result;
    }
    /**
     * ���ض��˵�
     * @param $bill_date ���ض��˵������ڣ���ʽ��20140603
     * @param $bill_type ����
     * @return array
     */
    public function downloadBill($bill_date,$bill_type = 'ALL'){
        $data = array();
        $data["appid"] = $this->_config["appid"];
        $data["mch_id"] = $this->_config["mch_id"];
        $data["bill_date"] = $bill_date;
        $data["bill_type"] = $bill_type;
        $data["nonce_str"] = $this->get_nonce_string();
        $result = $this->post(self::URL_DOWNLOADBILL, $data);
        return $result;
    }
    /**
     * ��ȡjs֧��ʹ�õĵڶ�������
     */
    public function get_package($prepay_id) {
        $data = array();
        $data["appId"] = $this->_config["appid"];
        $data["timeStamp"] = time();
        $data["nonceStr"]  = $this->get_nonce_string();
        $data["package"]   = "prepay_id=$prepay_id";
        $data["signType"]  = "MD5";
        $data["paySign"]   = $this->sign($data);
        return $data;
    }
    /**
     * ��ȡ���͵�֪ͨ��ַ������(��֪ͨ��ַ��ʹ��)
     * @return ������飬�������΢�ŷ��������͵����ݷ���null
     *          appid
     *          bank_type
     *          cash_fee
     *          fee_type
     *          is_subscribe
     *          mch_id
     *          nonce_str
     *          openid
     *          out_trade_no    �̻�������
     *          result_code
     *          return_code
     *          sign
     *          time_end
     *          total_fee       �ܽ��
     *          trade_type
     *          transaction_id  ΢��֧��������
     */
    public function get_back_data() {
        $xml = file_get_contents("php://input");
        $data = $this->xml2array($xml);
        if ($this->validate($data)) {
            return $data;
        } else {
            return null;
        }
    }
    /**
     * ��֤����ǩ��
     * @param $data ��������
     * @return ����У����
     */
    public function validate($data) {
        if (!isset($data["sign"])) {
            return false;
        }
        $sign = $data["sign"];
        unset($data["sign"]);
        return $this->sign($data) == $sign;
    }
    /**
     * ��Ӧ΢��֧����̨֪ͨ
     * @param $return_code ����״̬�� SUCCESS/FAIL
     * @param $return_msg  ������Ϣ
     */
    public function response_back($return_code="SUCCESS", $return_msg=null) {
        $data = array();
        $data["return_code"] = $return_code;
        if ($return_msg) {
            $data["return_msg"] = $return_msg;
        }
        $xml = $this->array2xml($data);
        print $xml;
    }
}
?>