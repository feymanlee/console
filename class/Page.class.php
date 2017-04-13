<?php


//----------------------------------
// 分页类2015Ajax加强版
//----------------------------------
class Page
{
    public  $prev       = '上一页';
    public  $next       = '下一页';
    public  $start      = '首页';
    public  $end        = '尾页';
    public  $countStart = '共计';
    public  $countEnd   = '条数据';
    public  $fo         = '从';
    public  $line       = '条';
    public  $listNum    = 4;
    public  $shift;    //偏移量
    public  $row;    //每页行数
    private $startNum; //开始行
    private $endNum;    //结束行
    private $cPage;  //当前页
    private $pageNumber;  //总页数
    private $DataCount; //总行数
    private $uri;
    private $pkey       = 'p';
    private $skey       = 'rows';
    private $isjs;

    /**
     * 初始化分页对象
     *
     * @param Int $count 总数行数
     * @param Int $rows  每页显示行数
     *
     * @return this
     */
    public function __construct($count, $rows)
    {
        $this->isjs       = !empty($_GET['tojs']) ? $_GET['tojs'] : NULL;
        $this->DataCount  = $count;
        $this->row        = !empty($_GET[$this->skey]) ? Intval($_GET[$this->skey]) : $rows;
        $this->cPage      = $this->getCpage();
        $this->pageNumber = $this->getCount();
        $this->startNum   = $this->startRow();
        $this->endNum     = $this->endRow();
        $this->shift      = $this->startNum - 1;
        $this->uri        = $this->setUri();

    }


    /**
     * 取分页数据(可根据需求调整显示顺序)
     *
     * @param String $limit 要获取的数据块，如：'2,3,4'  1.起始页  2.上一页  3.分页   4.下一页   5.末尾页   6.总页数   7.范围   8.总行数
     *
     * @return mixed
     */
    public function getPage($limit = '1,2,3,4,5,6,7,8')
    {
        if ($limit == false) {
            return ['count' => $this->DataCount, 'pages' => $this->pageNumber, 'rows' => $this->row];
        }
        $getKey = explode(',', $limit);
        //起始页
        $page[1] = $this->getStart();
        //上一页
        $page[2] = $this->getPrev();
        //分页
        $page[3] = $this->getPages();
        //下一页
        $page[4] = $this->getNext();
        //末尾页
        $page[5] = $this->getEnd();
        //当前显示页与总页数
        $page[6] = "<span class='Pnumber'>{$this->cPage}/{$this->pageNumber}</span>";
        //本页显示开始与结束行
        $page[7] = "<span class='Pline'>{$this->fo}" . $this->startNum . "-" . $this->endNum . "{$this->line}</span>";
        //行数
        $page[8] = "<span class='Pcount'>{$this->countStart}{$this->DataCount}{$this->countEnd}</span>";
        //构造返回数据
        $retuls = '<div>';
        foreach ($getKey as $key => $value) {
            $retuls .= $page[$value];
        }
        $retuls .= '</div>';

        //返回分页数据
        return $retuls;
    }

    //从多少行开始
    private function startRow()
    {
        return ($this->cPage - 1) * $this->row + 1;
    }

    //取当前页
    private function getCpage()
    {
        if (!empty($_GET[$this->pkey])) {
            if ($_GET[$this->pkey] >= 1 and $_GET[$this->pkey] <= $this->DataCount) {
                $cp = $_GET[$this->pkey];
            } else {
                $cp = 1;
            }
        } else {
            $cp = 1;
        }

        return $cp;
    }

    //从多少行结束
    private function endRow()
    {
        return $this->cPage * $this->row;
    }

    //构造分页
    private function getPages()
    {
        $pages = '';
        //当前页之前
        if (($this->pageNumber - $this->cPage) < $this->listNum) {
            $num = $this->listNum + ($this->listNum - ($this->pageNumber - $this->cPage));
        } else {
            $num = $this->listNum;
        }
        for ($i = $num; $i >= 1; $i--) {
            $page = $this->cPage - $i;
            if ($page > 0) {
                $pages .= $this->isjs == NULL ? "<a class='Pnum' href='{$this->uri}{$this->pkey}={$page}'>{$page}</a>" : "<a class='Pnum' onclick='{$this->isjs}({$page})'>{$page}</a>";
            }

        }
        //当前页
        if ($this->cPage > 0) {
            $pages .= "<span class='Pcurrent'>{$this->cPage}</span>";
        }
        //当前页之后
        if ($this->cPage <= $this->listNum) {
            $num = $this->listNum + ($this->listNum - $this->cPage) + 1;
        } else {
            $num = $this->listNum;
        }
        for ($i = 1; $i <= $num; $i++) {
            $page = $this->cPage + $i;
            if ($page > $this->pageNumber) {
                break;
            }
            $pages .= $this->isjs == NULL ? "<a class='Pnum' href='{$this->uri}{$this->pkey}={$page}'>{$page}</a>" : "<a class='Pnum' onclick='{$this->isjs}({$page})'>{$page}</a>";
        }

        return $pages;
    }

    //构造下一页
    private function getNext()
    {
        if ($this->cPage >= $this->pageNumber) {
            $Str = '';
        } else {
            $Str = $this->isjs == NULL ? "<a class='Pnext' href='{$this->uri}{$this->pkey}=" . ($this->cPage + 1) . "'>{$this->next}</a>" : "<a class='Pnext' onclick='{$this->isjs}(" . ($this->cPage + 1) . ")'>{$this->next}</a>";
        }

        return $Str;
    }

    //构造末尾页
    private function getEnd()
    {
        if ($this->cPage >= $this->pageNumber) {
            $Str = '';
        } else {
            $Str = $this->isjs == NULL ? "<a class='Pend' href='{$this->uri}{$this->pkey}={$this->pageNumber}'>{$this->end}</a>" : "<a class='Pend' onclick='{$this->isjs}({$this->pageNumber})'>{$this->end}</a>";
        }

        return $Str;
    }

    //构造上一页
    private function getPrev()
    {
        if ($this->cPage == 1) {
            $Str = '';
        } else {
            $Str = $this->isjs == NULL ? "<a class='Ppren' href='{$this->uri}{$this->pkey}=" . ($this->cPage - 1) . "'>{$this->prev}</a>" : "<a class='Ppren' onclick='{$this->isjs}(" . ($this->cPage - 1) . ")'>{$this->prev}</a>";
        }

        return $Str;
    }

    //构造起始页
    private function getStart()
    {
        if ($this->cPage == 1) {
            $Str = '';
        } else {
            $Str = $this->isjs == NULL ? "<a class='Pstart' href='{$this->uri}{$this->pkey}=1'>{$this->start}</a>" : "<a class='Pstart' onclick='{$this->isjs}(1)'>{$this->start}</a>";
        }

        return $Str;
    }

    //构造URI
    private function setUri()
    {
        $url = $_SERVER['REQUEST_URI'];
        if (strstr($url, '?')) {
            //获取URL参数
            $arr = parse_url($url);
            //将URL转成数组
            if (isset($arr['query'])) parse_str($arr['query'], $output);
            //删除分页参数
            unset($output[$this->pkey]);
            //将数组转换成URL
            $null = '';
            if (count($output) > 0) {
                $null = '&';
            }
            $url = $arr['path'] . '?' . http_build_query($output) . $null;
        } else {
            $url .= '?';
        }

        //返回URI
        return $url;
    }

    //计算分页数
    private function getCount()
    {
        return Intval(ceil($this->DataCount / $this->row));
    }
}

?>