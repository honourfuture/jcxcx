using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Text;
using System.Windows.Forms;
using System.Web;


namespace WindowsApplication1

{

   
    public partial class Form1 : Form
    {

        zzsms.info zz = new zzsms.info();

       
        public Form1()
        {
            InitializeComponent();
        }


         public static string UrlEncode(string str)
        {
            StringBuilder sb = new StringBuilder();
            byte[] byStr = System.Text.Encoding.Default.GetBytes(str);
            for (int i = 0; i < byStr.Length; i++)
            {
                sb.Append(@"%" + Convert.ToString(byStr[i], 16));
            }
            
            return (sb.ToString());
        }

        private void button1_Click(object sender, EventArgs e)
        {
            string res="";

         
            res = zz.sendSMS("ZXHD-CRM-0100-XXXXXX", "����",
                            "139107����������"
                                    , UrlEncode("����;���%"),
                                          "", "1", "", "1", "", "4");

           

         //   MessageBox.Show(HttpUtility.UrlEncode("�O���{���B", System.Text.Encoding.GetEncoding("GBK")));
          if (res.Equals("0"))
          {
              MessageBox.Show("succ");

          }
          else
          {
              MessageBox.Show("fail" + res);
          }
        }

        private void button2_Click(object sender, EventArgs e)
        {
           MessageBox.Show( zz.getbalance("ZXHD-CRM-0100-******", "����"));
          
        }

        private void button3_Click(object sender, EventArgs e)
        {
            //

            MessageBox.Show(  zz.register("ZXHD-CRM-0100-XXXXXX", "�Լ��ʻ�������", " ��ҵ����", "���", "��ַ", 
                "�绰", "��ϵ��", "email", "fax", "�ʱ�", "�ֻ�")); 
            //ע�ᣨ���ʻ���һ��ʹ��ʱִ��һ�θ÷����Ϳ��ԣ�����������ʵ�ʷ���Ϊ׼��
        }

        private void button4_Click(object sender, EventArgs e)
        {
            MessageBox.Show(zz.getmo("ZXHD-CRM-0100-******", "����"));//���ؽ�����ӿ��ĵ�˵������
        }

        private void button5_Click(object sender, EventArgs e)
        {
            MessageBox.Show(zz.getReport("ZXHD-CRM-0100-******", "����"));//���ؽ�����ӿ��ĵ�˵������
        }
    }
}