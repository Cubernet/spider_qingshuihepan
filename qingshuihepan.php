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
    requests::add_cookies("v3hW_2132_saltkey=FgITillK; v3hW_2132_lastvisit=1476982663; v3hW_2132_secqaa=1129.17db908debef09242e; v3hW_2132_auth=69c5Hg574q1Aah8Yd6nIBgJ8toziah%2FVcYEXOZHSF4%2FQUY7Ztk%2Bxw4M4uUIG9NkZPKHRZDEc7BVgFzlPOM02p5RcurQ; v3hW_2132_lastcheckfeed=179270%7C1476986343; v3hW_2132_nofavfid=1; v3hW_2132_st_t=179270%7C1476988027%7Ca2f7da880de9a0ef9c478e238b3ecd0e; v3hW_2132_forum_lastvisit=D_326_1476986344D_316_1476986858D_174_1476988007D_109_1476988027; v3hW_2132_ulastactivity=a9a0qv6xYuI9EVDkhpOHltPrlqER2Mb4ORuos3K7oD6vLPWvKdcr; v3hW_2132_visitedfid=174D109D316D326; v3hW_2132_sendmail=1; v3hW_2132_lip=117.139.249.197%2C1477070541; v3hW_2132_st_p=179270%7C1477070554%7C04aa97c792a31d0f282b52ad3cb4c7fd; v3hW_2132_viewid=tid_1224048; v3hW_2132_sid=Jq3e13; v3hW_2132_lastact=1477070554%09home.php%09spacecp; CNZZDATA5360190=cnzz_eid%3D1242085639-1472259404-%26ntime%3D1477067400; v3hW_2132_smile=10D1");
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
