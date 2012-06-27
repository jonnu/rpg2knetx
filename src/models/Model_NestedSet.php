<?php

class Model_NestedSet extends CI_Model {
    
    const AT_HEAD   = 0x01;     // Stick at the head
    const AT_TAIL   = 0x02;     // Stick at the tail

    protected $_ns_table;       // e.g. page
    protected $_ns_pk_field;    // e.g. page_id
    protected $_ns_ln_field;    // e.g. page_left
    protected $_ns_rn_field;    // e.g. page_right


    /**
     * __construct
     *
     * @param string $table         name of the database table
     * @param string $table_pk      name of the primary key field (usually an ID field)
     * @param string $table_left    name of the nested set left-hand meta field
     * @param string $table_right   name of the nested set right-hand meta field
     *
     * @return void
     *
     **/
    public function __construct($table = null, $table_pk = null, $table_left = null, $table_right = null) {

        $this->_ns_table    = $table;
        $this->_ns_pk_field = $table_pk;
        $this->_ns_ln_field = $table_left;
        $this->_ns_rn_field = $table_right;
        
        if (is_null($this->_ns_table)) {
            $this->enrich();
        }

        parent::__construct();
    }


    /**
     * enrich
     * 
     * 'Guess' the names of internal field names based on the
     * name of the model.
     *
     * @return void
     *
     **/
    private function enrich() {

        $current_model_name = get_class($this);
        $this->_ns_table    = strtolower(substr($current_model_name, 0, strrpos($current_model_name, '_')));
        $this->_ns_pk_field = sprintf('%s_id', $this->_ns_table);
        $this->_ns_ln_field = sprintf('%s_left', $this->_ns_table);
        $this->_ns_rn_field = sprintf('%s_right', $this->_ns_table);
    }


    /**
     * path
     *
     * Return a string containing a URI built from gluing the
     * specified field together repeatedly (usually a slug).
     *
     * @param int $leaf_id
     * @param string $leaf_slug
     * @param string $leaf_glue
     *
     * @return string
     **/
    public function path($leaf_id, $leaf_slug = null, $leaf_glue = '/') {

        if (is_null($leaf_slug)) {
            $leaf_slug = sprintf('%s_slug', $this->_ns_table);
        }

        $this->db->select('node.*');
        $this->db->from($this->_ns_table . ' leaf');
        $this->db->from($this->_ns_table . ' node');
        $this->db->where(sprintf('leaf.%1$s between node.%1$s and node.%2$s', $this->_ns_ln_field, $this->_ns_rn_field));
        $this->db->where(sprintf('leaf.%1$s', $this->_ns_pk_field), $leaf_id);
        $this->db->order_by(sprintf('leaf.%1$s', $this->_ns_ln_field));
        $node_result = $this->db->get();
        
        $components = array();
        foreach ($node_result->result() as $node) {
            $components[] = $node->$leaf_slug;
        }
        
        return implode($leaf_glue, $components);
    }

    /*
    public function delete($page_id) {
        
        // Get the core item & delete it.
        $page = $this->retrieve_by_id($page_id);
        $this->db->where('page_left between ' . $page->tree_left() . ' and ' . $page->tree_right());
        $this->db->delete('page');
        
        // Store the deleted items for flash message.
        $deleted_items = $this->db->affected_rows();
        
        // Right
        $this->db->set('page_right', 'page_right - ' . $page->tree_width(), false);
        $this->db->where('page_right > ' . $page->tree_right());
        $this->db->update('page');
        
        // Left
        $this->db->set('page_left', 'page_left - ' . $page->tree_width(), false);
        $this->db->where('page_left > ' . $page->tree_right());
        $this->db->update('page');
        
        // Write the flash message.
        $this->session->set_flashdata('admin/message', sprintf('Deleted %d page%s', $deleted_items, $deleted_items == 1 ? '' : 's'));
        
        return $deleted_items === 0 ? false : $deleted_items;
    }


    public function retrieve_by_id($item_id) {

        $this->db->select('pn.*');
        $this->db->select('
        ifnull((
            select pp.page_id
                from page pp
                    where pp.page_left < pn.page_left AND pp.page_right > pn.page_right
                order by
                    pp.page_right asc
                limit 1
        ), 0) as page_parent_id', false);
        $this->db->from('page pn');
        $this->db->from('page pp');
        $this->db->where('pn.page_id', $item_id);
        $this->db->select('group_concat(pp.page_slug order by pp.page_left separator "") as page_slug_path', false);
        $this->db->where('pn.page_left between pp.page_left and pp.page_right');
        $this->db->order_by('pn.page_left');
        $this->db->group_by('pn.page_id');
        $page_result = $this->db->get();
        
        if($page_result->num_rows() !== 1) {
            return false;
        }

        return $page_result->row(0, 'Page_Object');
    }*/


    /**
     * retrieve_nested
     * 
     * Obtain a nested 'tree' array of objects from
     * the database.  Optionally, supply an ID for which
     * record you would like the root to be (which can 
     * be used to obtain a sub-tree).
     *
     * @param   mixed $object_name
     * @param   int   $element_root
     * @param   int   $element_limit  
     *
     * @return  array
     */
    public function retrieve_nested($object_name = null, $element_root = 0, $element_limit = null) {

        if (is_null($object_name)) {
            $object_name = ucfirst($this->_ns_table) . '_Object';
        }

        if (!class_exists($object_name, true)) {
            throw new NestedSet_Exception('Class "' . $object_name . '" does not exist');
        }

        if (!array_key_exists('Nestable_Object', class_parents($object_name))) {
            throw new NestedSet_Exception('Class "' . $object_name . '" is not a nestable object');
        }

        $this->db->select('leaf.*');
        $this->db->select(sprintf('GROUP_CONCAT(node.%2$s ORDER BY node.%1$s SEPARATOR "%3$s") AS forum_path', $this->_ns_ln_field, 'forum_slug', '/'), false);
        $this->db->select(sprintf('GROUP_CONCAT(NULLIF(node.%1$s, leaf.%1$s) ORDER BY node.%2$s) AS forum_id_path', $this->_ns_pk_field, $this->_ns_ln_field), false);

        $this->db->from($this->_ns_table . ' node');
        $this->db->from($this->_ns_table . ' leaf');

        $this->db->where(sprintf('(leaf.%1$s BETWEEN node.%1$s AND node.%2$s)', $this->_ns_ln_field, $this->_ns_rn_field));
        
        $this->db->group_by(sprintf('leaf.%s', $this->_ns_pk_field));
        $this->db->order_by(sprintf('leaf.%s', $this->_ns_ln_field));

        // If we need to obtain a sub-tree, we need to join
        // an additional object table plus the sub-tree to cross-reference
        if ($element_root != 0 && is_numeric($element_root)) {
        
            $this->db->select(sprintf('NULLIF(sub_leaf.%1$s, leaf.%1$s) AS forum_parent_id', $this->_ns_pk_field), false);
            $this->db->from(sprintf('%s sub_node', $this->_ns_table));
            $this->db->from(sprintf('(
                    SELECT
                        leaf.%2$s,
                        (count(node.%2$s) - 1) as forum_depth
                    FROM
                        %1$s AS leaf,
                        %1$s AS node
                    where
                        (leaf.%3$s BETWEEN node.%3$s AND node.%4$s)
                            AND leaf.%2$s = %5$d
                        GROUP BY leaf.%2$s
                        ORDER BY leaf.%3$s
                ) AS sub_leaf', 
                $this->_ns_table,
                $this->_ns_pk_field,
                $this->_ns_ln_field,
                $this->_ns_rn_field,
                $element_root
            ));

            $this->db->where(sprintf('(leaf.%1$s BETWEEN sub_node.%1$s and sub_node.%2$s)', $this->_ns_ln_field, $this->_ns_rn_field));
            $this->db->where(sprintf('(sub_node.%1$s = sub_leaf.%1$s)', $this->_ns_pk_field));
            $this->db->select(sprintf('(count(node.%1$s) - (sub_leaf.forum_depth + 1)) as forum_depth', $this->_ns_pk_field));

        }
        else {

            $this->db->select(sprintf('COUNT(node.%s) - 1 AS forum_depth', $this->_ns_pk_field));
            $this->db->select(sprintf('(
                SELECT
                    direct_node.%2$s
                FROM
                    %1$s direct_node
                WHERE
                    direct_node.%3$s < leaf.%3$s AND direct_node.%4$s > leaf.%4$s
                ORDER BY
                    direct_node.%4$s - leaf.%4$s ASC
                LIMIT
                    1
                ) AS forum_parent_id
            ', $this->_ns_table, $this->_ns_pk_field, $this->_ns_ln_field, $this->_ns_rn_field));

        }

        // Limit the depth of the query.
        if (!is_null($element_limit)) {
            $this->db->having($this->_ns_table . '_depth <= ' . $element_limit);
        }

        $object_results = $this->db->get();
        $nested_objects = $object_results->result($object_name);

        // Return the built tree
        return $this->build_tree($nested_objects);
    }




    /**
     * build_tree
     *
     *
     * @param array $objects
     *
     * @return array
     */
    protected function build_tree(array $objects) {

        $c_depth = 0;
        $nested  = array();
        $markers = array();
        $pointer = &$nested;

        foreach ($objects as $object) {

            // Interrogate object
            $o_id    = $object->id();
            $o_depth = $object->depth();

            if ($o_depth < $c_depth && $o_depth !== 0) {
                $pointer = &$markers[$o_depth - 1]->children;
            }
            elseif ($o_depth > $c_depth) {
                $pointer = &$markers[$c_depth]->children;
            }
            elseif ($o_depth == 0) {
                $pointer = &$nested;
            }

            // Update depth
            $c_depth = $o_depth;

            // Add to tree and update markers
            $pointer[$o_id]    = $object;
            $markers[$c_depth] = &$pointer[$o_id];
        }

        return $nested;
    }

    
    protected function tree_node_left($object_id = null) {
        return $this->tree_meta($this->_ns_ln_field, $object_id);
    }

    
    protected function tree_node_right($object_id = null) {
        return $this->tree_meta($this->_ns_rn_field, $object_id);
    }

    
    /**
     * tree_meta
     *
     * @return int
     */
    protected function tree_meta($meta_field, $object_id = null) {
        
        if (is_null($object_id)) {
            $meta_field = 'MAX(' . $meta_field . ')';
        }

        $this->db->select($meta_field . ' as meta_value');
        $this->db->from($this->_ns_table);
        
        if (!is_null($object_id)) {
            $this->db->where($this->_ns_pk_field, $object_id);
        }
        
        $meta_result = $this->db->get();

        if ($meta_result->num_rows() === 0) {
            return 0;
        }

        return (int)$meta_result->row('meta_value');
    }
    
}



abstract class Nestable_Object {

    private $key;

    public function __construct() {

        $child_class = get_class($this);
        $this->key   = strtolower(substr($child_class, 0, strrpos($child_class, '_')));
    }

    public function id() {
        return (int)$this->property('id');
    }

    public function depth() {
        return (int)$this->property('depth');
    }

    public function left() {
        return (int)$this->property('left');
    }

    public function right() {
        return (int)$this->property('right');
    }

    public function hasChildren() {
        return $this->right() - $this->left() > 1;
    }

    private function property($property) {
        return $this->{$this->key . '_' . $property}; 
    }
}



class NestedSet_Exception extends Exception {}