<?php
/**
 * tree 基类
 *
 * PHP version 5
 *
 * @package   LS
 * @author    Fee <Fee@shorigo.com>
 * @copyright 2015 SRG Inc.
 */
namespace Org\Util;
class Tree {
    private $_data      = array();
    private $_children  = array();
    private $_parent    = array();
    private $_layer     = array(0 => 0); // root的layer为0
    public  $rootId     = 0; //根节点Id

    /**
     * 构造函数
     */
    public function __construct()
    {
        // 设置根结点（根结点的id为0，父结点为-1）
        $this->setNode(0, -1, 'root');
    }

    /**
     * 设置结点
     *
     * @param int $id       结点id
     * @param int $parentId 父结点id
     * @param mix $value    结点数据
     *
     * @return void
     */
    public function setNode($id, $parentId, $value)
    {
        // 设置$data
        $this->_data[$id] = $value;

        // 设置$child
        if (!isset($this->_children[$id])) {
            $this->_children[$id] = array();
        }

        if (isset($this->_children[$parentId])) {
            $this->_children[$parentId][] = $id;
        } else {
            $this->_children[$parentId] = array($id);
        }

        // 设置$parent
        $this->_parent[$id] = $parentId;
    }

    /**
     * 取得按先根遍历排序的结点值、结点层次、是否叶子结点
     *
     * @return array       array('data' => ?, 'layer' => 1, 'isLeaf' => true)
     *                     array('data' => ?, 'layer' => 2, 'isLeaf' => false)
     */
    public function getSortedNodes()
    {
        $stack = isset($this->_children[$this->rootId]) ? $this->_children[$this->rootId] : array();
        $data = array();
        while (!empty($stack)) {
            $id = array_shift($stack);
            $layerId = isset($this->_layer[$this->_parent[$id]]) ?  $this->_layer[$this->_parent[$id]] : 0;
            $this->_layer[$id] = $layerId + 1;
            $data[] = array(
                'data'  => $this->_data[$id], 'layer' => $this->_layer[$id], 'isLeaf' => empty($this->_children[$id])
            );
            if (!empty($this->_children[$id])) {
                $stack = array_merge($this->_children[$id], $stack);
            }
        }

        return $data;
    }
}
?>