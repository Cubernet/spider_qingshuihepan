<?php
ini_set("memory_limit", "1024M");
require dirname(__FILE__).'/core/init.php';

/* Do NOT delete this comment */
/* 不要删除这段注释 */

$configs = array(
    'name' => '清水河畔',
    'tasknum' => 16,
    //'save_running_state' => true,
    'log_show' => false,
    'domains' => array(
        'bbs.uestc.edu.cn'
    ),
    'scan_urls' => array(
        "http://bbs.uestc.edu.cn",
        // "http://bbs.uestc.edu.cn/forum.php?mod=forumdisplay&fid=174",
        // "http://bbs.uestc.edu.cn/forum.php?mod=forumdisplay&fid=61&page=2",
    ),
    'list_url_regexes' => array(
        // "http://bbs.uestc.edu.cn",
        "http://bbs.uestc.edu.cn/forum.php\?mod=forumdisplay&fid=\d+&{0,1}[page]{0,1}={0,1}\d*",     
    ),
    'content_url_regexes' => array(
        "http://bbs.uestc.edu.cn/forum.php\?mod=viewthread&tid=\d+",
    ),
    'export' => array(
        // 'type' => 'csv',
        // 'file' => '/Users/Cubernet/Desktop/phpspider-master/1.csv',
        'type' => 'db',
        'table' => 'qingshuihepan',
    ),
    'fields' => array(
        array(
            'name' => "title",
            'selector' => "//span[contains(@id,'thread_subject')]",
            'required' => false,
        ),
        array(
            'name' => "type",
            'selector' => "//div[contains(@class,'bm cl')]//div[contains(@class,'z')]/a[4]",
            'required' => false,
        ),
        array(
            'name' => "author",
            'selector' => "//div[contains(@class,'authi')]//a[contains(@class,'xw1')]",
            'required' => false,
        ),
        array(
            'name' => "views",
            'selector' => "//div[contains(@class,'hm ptn')]//span[2]",
            'required' => false,
        ),
        array(
            'name' => "comments",
            'selector' => "//div[contains(@class,'hm ptn')]//span[5]",
            'required' => false,
        ),
        array(
            'name' => "date",
            'selector' => "//div[contains(@class,'authi')]//em",
            'required' => false,
        ),
    ),
);

$spider = new phpspider($configs);
$spider->on_start = function($phpspider) 
{
    requests::add_header('Referer','http://bbs.uestc.edu.cn/member.php?mod=logging&action=login');
    requests::add_cookies("");
    requests::add_cookies("NAME","bbs.uestc.edu.cn");
    // print_r('--1--');
};

$spider->on_scan_page = function($page, $content, $phpspider) 
{
    $regex = "#http://bbs.uestc.edu.cn/forum.php\?mod=forumdisplay&fid=\d+#";
    $urls = array();
    preg_match_all($regex, $content, $out);

    $urls = empty($out[0]) ? array() : $out[0];
    if (!empty($urls)) {
        foreach ($urls as $url) 
        {   
            $options = array(
                'url_type' => $url,
                'method' => 'get',
            );
            $phpspider->add_url($url, $options);    
            // print($url);   
        }
    }
    return false;
};

$spider->on_list_page = function($page, $content, $phpspider) 
{   
    if (preg_match("#page#", $page['request']['url'])){
        $regex = "#&tid=(\d+)#";
        $urls = array();
        preg_match_all($regex, $content, $out);
        $urls = empty($out[1]) ? array() : $out[1];
        if (!empty($urls)) {
            foreach ($urls as $v) 
            {   
                $url = "http://bbs.uestc.edu.cn/forum.php?mod=viewthread&tid={$v}";
                $options = array(
                    'url_type' => $url,
                    'method' => 'get',
                );
                $phpspider->add_url($url, $options);  
            }
        }
    }
    else{
        // print_r("---!!!---");
        preg_match_all("#http://bbs.uestc.edu.cn/forum.php\?mod=forumdisplay&fid=(\d+)#", $page['request']['url'], $idarray);
        $id = $idarray[1][0];
        // print_r($page['request']['url']);

        $regex = "#http://bbs.uestc.edu.cn/forum.php\?mod=forumdisplay&fid={$id}.*?page=(\d+)#";
        preg_match_all($regex, $content, $out);

        $index = max($out[1]);
        for ($i = 2; $i <= $index; $i++) {
            $url = "http://bbs.uestc.edu.cn/forum.php?mod=forumdisplay&fid={$id}&page={$i}";
            $options = array(
                    'url_type' => $url,
                    'method' => 'get',
                );
            $phpspider->add_url($url, $options);
        }
    }
    return false;
};

$spider->on_extract_field = function($fieldname, $data, $page) 
{
    if ($fieldname == 'date') 
    {
        // print_r("=========");
        preg_match_all("#\d+-\d+-\d+\s\d+:\d+:\d+#", $data, $out);
        // $data = trim(str_replace(array("发表于","/"),"", strip_tags($data)));
        $data = $out[0][0];
        // print_r($data);
    }
    return $data;
};

$spider->start();
