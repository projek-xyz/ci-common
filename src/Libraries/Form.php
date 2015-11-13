<?php
namespace Projek\CI\Common\Libraries;

class Form
{
    /**
     * Codeigniter superobject
     *
     * @var  mixed
     */
    protected $CI;

    /**
     * Make use of common traits
     */
    use App_CommonErrors;

    /**
     * Is it a Horizontal Form?
     *
     * @var  bool
     */
    protected $is_hform = true;

    /**
     * Is it a Multipart Form?
     *
     * @var  bool
     */
    protected $is_multipart = false;

    /**
     * Is it doesn't need any buttons?
     *
     * @var  bool
     */
    protected $no_buttons = false;

    /**
     * Is it has any fieldsets?
     *
     * @var  bool
     */
    private $has_fieldset = false;

    /**
     * Form fields placeholder
     *
     * @var  array
     */
    protected $_fields = [];

    /**
     * Form buttons placeholder
     *
     * @var  array
     */
    protected $_buttons = [];

    /**
     * Form Fields counters
     *
     * @var  array
     */
    protected $_counts = [];

    /**
     * Field templates
     *
     * @var  array
     */
    protected $_template = [
        'group_open'     => '<div class="%s" %s>',
        'group_close'    => '</div>',
        'group_class'    => 'form-group',
        'label_open'     => '<label class="%s" %s>',
        'label_close'    => '</label>',
        'label_class'    => 'control-label',
        'label_cols'     => 'md-3,sm-4',
        'field_open'     => '<div class="%s" %s>',
        'field_close'    => '</div>',
        'field_class'    => 'form-control input ',
        'field_cols'     => 'md-9,sm-8',
        'buttons_class'  => 'btn',
        'required_attr'  => ' <abbr title="%s">*</abbr>',
        'desc_open'      => '<span class="help-block">',
        'desc_close'     => '</span>',
        'editor_filters' => [],
    ];

    /**
     * Main Form Field Attributes
     *
     * @var  array
     */
    protected $_attrs = [
        'action'  => null,
        'name'    => null,
        'id'      => null,
        'class'   => null,
        'method'  => 'post',
        'extras'  => [],
        'hiddens' => [],
    ];

    /**
     * Defualt field attributes, in case you forget to give it an value, you'll
     * get an empty string from this
     *
     * @var  array
     */
    protected $_default_attr = [
        'name'       => '',
        'label'      => '',
        'desc'       => '',
        'type'       => '',
        'value'      => '',
        'std'        => '',
        'attr'       => '',
        'validation' => ''
    ];

    /**
     * Default class constructor
     */
    public function __construct(array $attrs = [])
    {
        // Load CI super object
        $this->CI =& get_instance();

        // Load dependencies
        $this->CI->load->library('form_validation');
        $this->CI->load->helper('language');

        if (!empty($attrs)) {
            $this->initialize($attrs);
        }
    }

    /**
     * Initializing new form
     *
     * @param   array   $attrs  Form Attributes config
     * @return  mixed
     */
    public function initialize(array $attrs = [])
    {
        if ($template = config_item('form_template')) {
            $this->set_template($template);
        }

        if (isset($attrs['template'])) {
            $this->set_template($attrs['template']);
        }

        // Applying default form attributes
        $attrs = elements([
            'action', 'id', 'name', 'class', 'method',
            'fields', 'buttons', 'no_buttons',
            'extras', 'hiddens', 'is_hform'
        ], $attrs, null);

        // Give some default values
        $this->_attrs['action'] = current_url();
        $this->_attrs['name']   = 'form-'.str_replace('/', '-', uri_string());

        foreach (array_keys($this->_attrs) as $attr_key) {
            if (null !== $attrs[$attr_key]) {
                $this->_attrs[$attr_key] = $attrs[$attr_key];
            }
        }

        $this->_attrs['role'] = 'form';

        if (null === $attrs['id']) {
            $this->_attrs['id'] = $this->_attrs['name'];
        }

        if (null !== $attrs['is_hform']) {
            $this->is_hform = $attrs['is_hform'];
        }

        // if fields is already declarated in the config, just make it happen ;)
        if (null !== $attrs['fields']) {
            $this->add_fields($attrs['fields']);
        }

        // if buttons is already declarated in the config, just make it happen ;)
        if (null !== $attrs['buttons']) {
            $this->add_buttons($attrs['buttons']);
        }

        // set this up and you'll lose your buttons :P
        if (null !== $attrs['no_buttons']) {
            $this->no_buttons = $attrs['no_buttons'];
        }

        return $this;
    }

    /**
     * Setup default field template
     * If you want to replace the default value, just pass these key(s) with
     * your value, and you'll see your own field template
     *
     * @param   array  $template  Template replacements
     * @return  mixed
     */
    public function set_template(array $template)
    {
        $valid_tmpl = array_keys($this->_template);

        foreach ($valid_tmpl as $option) {
            if (isset($template[$option])) {
                $this->_template[$option] = $template[$option];
            }
        }

        return $this;
    }

    /**
     * Setup multiple form fields at once.
     *
     * @param   array  $fields  Fields declaration
     * @return  mixed
     */
    public function add_fields(array $fields)
    {
        foreach ($fields as $field_id => $attributes) {
            $this->add_field($field_id, $attributes);
        }

        return $this;
    }

    /**
     * Setup form multiple form buttons at once
     *
     * @param   array  $buttons  Form buttons
     * @return  mixed
     */
    public function add_buttons(array $buttons)
    {
        foreach ($buttons as $button_id => $attributes) {
            $this->add_button($button_id, $attributes);
        }

        return $this;
    }

    /**
     * Setup single form field.
     *
     * @param   string  $field_id    Field identifier
     * @param   array   $attributes  Field attributes
     * @return  mixed
     */
    public function add_field($field_id, array $attributes)
    {
        if (in_array($attributes['type'], ['file', 'upload'])) {
            $this->is_multipart = true;
        }

        // Make sure that you have no duplicated field name
        if (is_numeric($field_id) and isset($attributes['name'])) {
            $field_id = $attributes['name'];
        } elseif (is_string($field_id) and !isset($attributes['name'])) {
            $attributes['name'] = $field_id;
        }

        $this->_fields[$field_id] = $attributes;

        return $this;
    }

    /**
     * Setup single form buttons
     *
     * @param   string  $button  Button identifier
     * @param   array   $button  Button attributes
     * @return  mixed
     */
    public function add_button($button_id, array $attributes)
    {
        // Make sure that you have no duplicated field name
        if (is_numeric($button_id) and isset($attributes['name'])) {
            $button_id = $attributes['name'];
        } elseif (is_string($button_id) and !isset($attributes['name'])) {
            $attributes['name'] = $button_id;
        }

        $this->_buttons[$button_id] = $attributes;

        return $this;
    }

    /**
     * Generate everything that we've set above
     *
     * @return  string
     */
    public function generate()
    {
        // remove Form action out from Attributes
        $_action = $this->_attrs['action'];
        unset($this->_attrs['action']);

        // If you have form hidden, put them to new variable
        $_hiddens = $this->_attrs['hiddens'] ?: [];
        unset($this->_attrs['hiddens']);

        // is it an upload form?
        if (true === $this->is_multipart) {
            $this->_attrs['enctype'] = 'multipart/form-data';
            // $this->CI->load->library('biupload');
            // $form .= $this->CI->biupload->template();
        }

        // make it horizontal form by default
        if (is_string($this->_attrs['class'])) {
            $this->_attrs['class'] = [$this->_attrs['class']];
        }

        // make it horizontal form by default
        if (true === $this->is_hform) {
            $this->_attrs['class'][] = 'form-horizontal';
        }

        $this->_attrs['class'] = implode(' ', $this->_attrs['class']);

        // If you have additional form attributes, merge it.
        $extras = $this->_attrs['extras'] ?: [];
        unset($this->_attrs['extras']);
        $this->_attrs += $extras;

        // Open up new form
        $form = form_open($_action, $this->_attrs, $_hiddens);

        // Loop the fields
        $this->_counts['feildsets'] = 0;

        foreach($this->_fields as $field_id => $field_attrs) {
            $form .= $this->_compile($field_id, $field_attrs);
        }

        // Close the fieldset before you close your form
        if ($this->_counts['feildsets'] > 0) {
            $form .= form_fieldset_close();
        }

        // Let them see your form has an action button(s)
        if (false === $this->no_buttons) {
            $form .= $this->_form_actions();
        }

        // Now you can close your form
        $form .= form_close();

        return $form;
    }

    /**
     * Compile all Fields that you've setup
     *
     * @param   array   $field_attrs  Field Attributes
     * @param   bool    $is_sub       Is it an sub-fields?
     * @return  string
     */
    protected function _compile($field_id, array $field_attrs, $is_sub = false)
    {
        $field_attrs = elements(array_keys($this->_default_attr), $field_attrs, '');

        $field_id or $field_id = isset($field_attrs['id']) ? $field_attrs['id'] : $field_attrs['name'];
        $field_attrs['id'] = str_replace('_', '-', $field_id);

        if ($field_attrs['type'] == 'hidden') {
            return form_hidden($field_attrs['name'], $field_attrs['std'], true).PHP_EOL;
        }

        if (empty($field_attrs['attr'])) {
            $field_attrs['attr'] = [];
        }

        // If it's a subfield and has a label, put it in placeholder attribute
        if ($is_sub and $field_attrs['label']) {
            $field_attrs['attr']['placeholder'] = $field_attrs['label'];
        }

        $html = '';

        switch ($field_attrs['type']) {
            case 'fieldset':
                // Form fieldset, make sure to close prior fieldset before creating new one.
                if ($this->_counts['feildsets'] > 0) {
                    $html .= form_fieldset_close().PHP_EOL;
                }

                $field_attrs['id'] = 'fieldset-'.$field_attrs['id'];
                $extras = $field_attrs['attr'] ?: [];

                // If your attributes is string, turn it into an array
                if (isset($field_attrs['attr']) and is_array($field_attrs['attr'])) {
                    $field_attrs = array_merge($field_attrs, $field_attrs['attr']);
                }

                // Call the fieldset and give it an ID with 'fieldset-' prefix
                $html .= form_fieldset($field_attrs['label'], $field_attrs).PHP_EOL;

                // indicate you have an opened fieldset
                $this->has_fieldset = true;
                $this->_counts['feildsets']++;
                break;

            case 'elert':
                $field_attrs['class'] || $field_attrs['class'] = 'default';

                if (in_array($field_attrs['class'], ['default', 'info', 'warning', 'danger', 'success'])) {
                    $field_attrs['class'] = 'alert-'.$field_attrs['class'];
                }

                $wrapper = ['id' => $field_attrs['id'], 'class' => 'form-info alert '.$field_attrs['class']];
                $html = '<div '.parse_html_attrs($wrapper).'>'.PHP_EOL;

                if ($field_attrs['label']) {
                    $html .= '<h3 class="form-info-heading">'.$field_attrs['label'].'</h3>'.PHP_EOL;
                }

                if ($field_attrs['std']) {
                    $content = $field_attrs['std'];
                    $content = is_array($content) ? implode('</p><p>', $content) : $content;
                    $html .= '<div class="form-info-content"><p>'.$content.'</p></div>'.PHP_EOL;
                }

                $html .= '</div>'.PHP_EOL;
                break;

            case 'subfield':
                $field_id  = 'sub'.element('id', $field_attrs, []);
                $input     = '<div id="'.html_escape($field_id).'" class="row">'.PHP_EOL;
                $subfields = element('fields', $field_attrs, []);
                $sf_total  = count($subfields);
                $sf_count  = 0;
                $errors    = [];

                foreach ($subfields as $sf_name => $sf_attrs) {
                    $sf_default = array_keys($this->_default_attr) + ['col'];
                    $sf_attrs = elements($sf_default, $sf_attrs, null);

                    if (null === $sf_attrs['name'] and is_string($sf_name)) {
                        $sf_attrs['name'] = $sf_name;
                    }

                    if (null === $sf_attrs['col']) {
                        $sf_attrs['col'] = floor(12 / $sf_total);
                    }

                    $input .= '<div class="'.grid_col('md-'.$sf_attrs['col']).'">'.PHP_EOL;

                    if (!empty($field_attrs['validation'])) {
                        $sf_attrs['validation'] = $field_attrs['validation'];
                    }

                    if (null === $sf_attrs['validation']) {
                        if (strpos('required', $sf_attrs['validation']) !== false) {
                            $sf_attrs['label'] .= ' &#42;';
                        }

                        $field_attrs['validation'] = $sf_attrs['validation'];
                    }

                    $sf_attrs['name'] = $field_attrs['name'].'_'.$sf_attrs['name'];
                    $sf_attrs['id']   = 'sub'.str_replace('_', '-', 'input-'.$sf_attrs['name']);
                    $sf_attrs['attr'] = !empty($sf_attrs['attr']) ? $sf_attrs['attr'] : $field_attrs['attr'];

                    if ($sf_count === 0) {
                        // $field_attrs['for'] = 'field-sub'.str_replace('_', '-', 'input-'.$field_id.'-'.$sf_attrs['id']);
                        $field_attrs['for'] = $sf_attrs['id'];
                    }

                    $input .= $this->_compile($field_id, $sf_attrs, true).PHP_EOL.'</div>'.PHP_EOL;

                    if ($is_error = form_error($sf_attrs['name'], $this->_template['desc_open'], $this->_template['desc_close'])) {
                        $errors[] = $is_error;
                    }

                    $sf_count++;
                }

                $input .= '</div>';

                if (count($errors) > 0) {
                    $field_attrs['desc']['err'] = $errors;
                }
                break;

            // Text Input fields
            // date, email, url, search, tel, password, text
            case 'email':
            case 'url':
            case 'search':
            case 'tel':
            case 'password':
            case 'number':
            case 'date':
            case 'text':
                $input = $this->_control_text($field_attrs);
                break;

            // Radiocheckbox field
            case 'radio':
            case 'checkbox':
                $input = $this->_control_radiocheck($field_attrs);
                break;

            // Selectbox field
            case 'multiselect':
            case 'dropdown':
                $input = $this->_control_selectbox($field_attrs);
                break;

            // Bootstrap Switch field
            case 'switch':
                $input = $this->_control_switch($field_attrs);
                break;

            // Textarea field
            // Using CI form_textarea() function.
            // adding jquery-autosize.js to make it more useful
            case 'textarea':
                $input = $this->_control_textarea($field_attrs);
                break;

            // Summernote editor
            case 'editor':
                $input = $this->_control_textrich($field_attrs);
                break;

            // Upload field
            // Using CI form_upload() function
            // Ajax Upload using FineUploader.JS
            case 'file':
            case 'upload':
                $input = $this->_control_upload($field_attrs);
                break;

            // Date picker field
            case 'datepicker':
                $input = $this->_control_datepicker($field_attrs);
                break;

            // Jquery-ui Slider
            case 'slider':
            case 'rangeslider':
                $input = $this->_control_slider($field_attrs);
                break;

            // Jquery-UI Spinner
            case 'spinner':
                $input = $this->_control_spinner($field_attrs);
                break;

            // Captcha field
            case 'captcha':
                $input = $this->_control_captcha($field_attrs);
                break;

            // Static field
            case 'static':
                $input = $this->_control_static($field_attrs);
                break;

            // Custom field
            case 'custom':
                $input = $field_attrs['std'];
                break;

            default:
                log_message('error', 'Form class with control type '.$field_attrs['type'].' is not supported (yet)');
                break;
        }

        if (isset($input)) {
            // $html .= $is_sub == false ? $this->_control($field_attrs, $input) : $input;
            $html .= $this->_control($field_attrs, $input).PHP_EOL;
        }

        return $html;
    }

    /**
     * Form field which commonly used by all field types
     *
     * @param   array    $attrs  Field Attributes
     * @param   string   $input  Field input html
     * @return  string
     */
    protected function _control($attrs, $input)
    {
        extract($this->_template);

        $attrs    = array_set_defaults($attrs, $this->_default_attr);
        $is_error = false;

        if (!is_array($attrs['desc'])) {
            $is_error = form_error($attrs['name'], $desc_open, $desc_close);
        } elseif (is_array($attrs['desc']) and isset($attrs['desc']['err'])) {
            $is_error = $attrs['desc'];
        }

        if (strlen(trim($attrs['validation'])) != 0) {
            if (false !== strpos($attrs['validation'], 'required')) {
                $attrs['label'] .= $required_attr;
                $group_class    .= ' form-required';
            }

            if ($is_error) {
                $group_class .= ' has-error';
            }
        }

        if (isset($attrs['class'])) {
            $group_class .= ' '.$attrs['class'];
        }


        $group_attr = 'id="group-'.str_replace('_', '-', $attrs['name']).'"';

        if (isset($attrs['fold']) and !empty($attrs['fold'])) {
            $fold_value = !is_array($attrs['fold']['value']) ? array($attrs['fold']['value']) : $attrs['fold']['value'];
            $fold_value = str_replace('"', '\'', json_encode($fold_value));

            $group_attr .= ' data-fold="1" data-fold-key="'.$attrs['fold']['key'].'" data-fold-value="'.$fold_value.'"';
        }

        $html      = sprintf($group_open, trim($group_class), $group_attr);
        $left_desc = (isset($attrs['left-desc']) and $attrs['left-desc'] == true);
        $errors    = ($is_error and !is_array($attrs['desc'])) ? $is_error : $attrs['desc'];

        if ($attrs['label'] != '') {
            // $label_class .= $label_col;
            $label_target = (isset($attrs['for']) ? $attrs['for'] : $attrs['id']);
            $label_col    = $this->is_hform ? $label_cols : '';

            $html .= '<div class="form-label '.$label_col.'">';
            $html .= form_label($attrs['label'], $label_target, array('class'=> $label_class));

            if ($left_desc) {
                $html .= $this->_form_desc($errors);
            }

            $html .= '</div>';
        }

        $input_col = $this->is_hform ? $field_cols : '';
        $html .= sprintf($field_open, 'form-input '.$input_col, '').$input;

        if (!$left_desc) {
            $html .= $this->_form_desc($errors);
        }

        $html .= $field_close.$group_close;

        return $html;
    }

    /**
     * Default CI Text Form
     *
     * @param   array  $field_attrs  Field Attributes
     * @return  string
     */
    protected function _control_text(array $field_attrs)
    {
        $field_attrs = array_set_defaults($field_attrs, [
            'type'  => 'text',
            'std'   => '',
            'class' => '',
            'attr'  => '',
        ]);

        $field_attrs['class'] .= ' '.$this->_template['field_class'];

        return form_input([
            'name'  => $field_attrs['name'],
            'type'  => $field_attrs['type'],
            'id'    => $field_attrs['id'],
            'class' => $field_attrs['class'],
        ], set_value($field_attrs['name'], $field_attrs['std']), $field_attrs['attr']);
    }

    /**
     * Default CI Textarea Form
     *
     * @param   array  $field_attrs  Field Attributes
     * @return  string
     */
    protected function _control_textarea(array $field_attrs)
    {
        // load_script('jq-autosize');

        $field_attrs = array_set_defaults($field_attrs, [
            'std'   => '',
            'rows'  => 3,
            'cols'  => '',
            'class' => '',
            'attr'  => '',
        ]);

        $field_attrs['class'] .= ' '.$this->_template['field_class'];

        return form_textarea([
            'name'  => $field_attrs['name'],
            'rows'  => $field_attrs['rows'],
            'cols'  => $field_attrs['cols'],
            'id'    => $field_attrs['id'],
            'class' => $field_attrs['class']
        ], set_value($field_attrs['name'], $field_attrs['std']), $field_attrs['attr']);
    }

    /**
     * Radio and Checkbox Form
     *
     * @param   array  $field_attrs  Field Attributes
     * @return  string
     */
    protected function _control_radiocheck(array $field_attrs)
    {
        $count  = 1;
        $input  = '';
        $field  = ($field_attrs['type'] == 'checkbox' ? $field_attrs['name'].'[]' : $field_attrs['name']);
        $devide = (count($field_attrs['option']) >= 6 ? true : false);

        if (!empty($field_attrs['option'])) {
            $output    = '';
            $actived   = false;
            $set_func  = 'set_'.$field_attrs['type'];
            $form_func = 'form_'.$field_attrs['type'];

            // if ( !is_array_assoc($field_attrs['option']) ) {
            //  $_tmp = $field_attrs['option'];
            //  $field_attrs['option'] = [];

            //  foreach ($_tmp as $opt) {
            //      $field_attrs['option'][$opt] = $opt;
            //  }
            // }

            foreach ($field_attrs['option'] as $value => $option) {
                if (is_array($field_attrs['std'])) {
                    if (($_key = array_keys($field_attrs['std'])) !== range(0, count($field_attrs['std']) - 1)) {
                        $field_attrs['std'] = $_key;
                    }

                    $actived = (in_array($value, $field_attrs['std']) ? true : false);
                } elseif (is_string($field_attrs['std'])) {
                    $actived = ($field_attrs['std'] == $value ? true : false);
                }

                $_id    = str_replace(array(' ', '_'), array('-', '-'), $field_attrs['name'].'-'.strtolower($value));
                $check  = '<div class="'.$field_attrs['type'].'" '.$field_attrs['attr'].'>'
                        . $form_func($field, $value, $set_func($field_attrs['name'], $value, $actived), 'id="'.$_id.'" '.$field_attrs['attr'])
                        . '<label for="'.$_id.'"> '.$option.'</label>'
                        . '</div>';

                $output .= ($devide ? '<div class="col-md-6">'.$check.'</div>' : $check);

                if ($devide AND $count % 2 == 0) {
                    $output .= '</div><div class="row">';
                }

                $count++;
            }

            return ($devide ? '<div class="row">'.$output.'</div>' : $output);
        }
    }

    /**
     * Default CI Text Form
     *
     * @param   array  $field_attrs  Field Attributes
     * @return  string
     */
    protected function _control_spinner(array $field_attrs)
    {
        // load_script('jqueryui-spinner');

        if (!isset($field_attrs['min'])) $field_attrs['min'] = 0;
        if (!isset($field_attrs['max'])) $field_attrs['max'] = 100;

        return form_input(array(
            'name'              => $field_attrs['name'],
            'id'                => $field_attrs['id'],
            'data-spinner-min'  => $field_attrs['min'],
            'data-spinner-max'  => $field_attrs['max'],
            'class'             => $this->_template['field_class'].' jqui-spinner'
        ), set_value($field_attrs['name'], $field_attrs['std']), $field_attrs['attr']);
    }

    /**
     * Default CI Text Form
     *
     * @param   array  $field_attrs  Field Attributes
     * @return  string
     */
    protected function _control_slider(array $field_attrs)
    {
        // load_script('jqueryui-slider');
        $field_attrs = array_set_defaults($field_attrs, [
            'min'  => 0,
            'max'  => 100,
            'step' => 1,
        ]);

        $slider_attrs = [
            'class'            => 'jqui-'.$field_attrs['type'],
            'data-slider-step' => $field_attrs['step'],
            'data-slider-min'  => $field_attrs['min'],
            'data-slider-max'  => $field_attrs['max'],
        ];

        if ($field_attrs['type'] == 'rangeslider') {
            if (!isset($std['min'])) $std['min'] = $field_attrs['min'];
            if (!isset($std['max'])) $std['max'] = $field_attrs['max'];

            $slider_attrs['data-slider-target-min'] = '#'.$field_attrs['id'].'-min';
            $slider_attrs['data-slider-target-max'] = '#'.$field_attrs['id'].'-max';

            $form_input = '<div class="input-group">'
                        . form_input([
                            'name'  => $field_attrs['name'].'_min',
                            'id'    => $field_attrs['id'].'-min',
                            'type'  => 'number',
                            'style' => 'width: 50%;',
                            'class' => $this->_template['field_class']
                        ], set_value($field_attrs['name'].'_min', $field_attrs['std']['min']), $field_attrs['attr'])
                        . form_input([
                            'name'  => $field_attrs['name'].'_max',
                            'id'    => $field_attrs['id'].'-max',
                            'type'  => 'number',
                            'style' => 'width: 50%;',
                            'class' => $this->_template['field_class']
                        ], set_value($field_attrs['name'].'_max', $field_attrs['std']['max']), $field_attrs['attr'])
                        . '</div>';
        } else {
            $slider_attrs['data-slider-target'] = '#'.$field_attrs['id'];

            $form_input = form_input([
                'name'  => $field_attrs['name'],
                'id'    => $field_attrs['id'],
                'type'  => 'number',
                'class' => $this->_template['field_class']
            ], set_value($field_attrs['name'], $field_attrs['std']), $field_attrs['attr']);
        }

        $input  = '<div class="row"><div class="col-sm-3">'.$form_input.'</div>'
                . '<div class="col-sm-9">'
                . '<div '.parse_attrs($slider_attrs).'></div>'
                . '</div></div>';

        return $input;
    }

    /**
     * Default CI Text Form
     *
     * @param   array  $field_attrs  Field Attributes
     * @return  string
     */
    protected function _control_textrich(array $field_attrs)
    {
        if (!isset($field_attrs['height'])) $field_attrs['height'] = 200;
        $lang = get_lang_code();

        // load_script('summernote-'.$lang);

        $field_name = $field_attrs['name'];
        $attrs = [
            'name'  => $field_name,
            'rows'  => '',
            'cols'  => '',
            'id'    => $field_attrs['id'],
            'class' => $this->_template['field_class'].' form-textrich',
            'data-edtr-height' => $field_attrs['height'],
            'data-edtr-fontname' => 'Arial',
        ];

        if (isset($field_attrs['extras']['fontname'])) {
            $attrs['data-edtr-fontname'] = $field_attrs['extras']['fontname'];
        }

        if ($locale = ($lang != 'en' ? $lang.'-'.strtoupper($lang) : '')) {
            $attrs['data-edtr-locale'] = $locale;
        }

        if (isset($this->_template['editor_filters'][$field_name])) {
            $patterns = $replacements = [];

            foreach ($this->_template['editor_filters'][$field_name] as $pattern => $replacement) {
                $patterns[] = $pattern;
                $replacements[] = $replacement;
            }

            $field_attrs['std'] = str_replace($patterns, $replacements, $field_attrs['std']);
        }

        return form_textarea($attrs, set_value($field_name, $field_attrs['std']), $field_attrs['attr']);
    }

    /**
     * Captcha Form
     *
     * @param   array  $field_attrs  Field Attributes
     * @return  string
     */
    protected function _control_captcha(array $field_attrs)
    {
        if (isset($field_attrs['mode']) and $field_attrs['mode'] == 'recaptcha') {
            $this->CI->load->helper('recaptcha');
            $output = recaptcha_get_html(Bootigniter::get_setting('auth_recaptcha_public_key'));
        } else {
            $captcha     = str_replace(FCPATH, '', config_item('bi_base_path'));
            $captcha_url = base_url($captcha.'libraries/vendor/captcha/captcha'.EXT);
            $image_id    = 'captcha-'.$field_attrs['id'].'-img';
            $input_id    = 'captcha-'.$field_attrs['id'].'-input';

            $output = img([
                'src'    => $captcha_url,
                'alt'    => 'Cool captcha image',
                'id'     => $image_id,
                'class'  => 'img',
                'width'  => '200',
                'height' => '70',
                'rel'    => 'cool-captcha'
            ]);

            $output .= anchor(current_url().'#', 'Ganti teks', [
                'class' => 'small change-image btn btn-default'
            ]);

            $output .= $this->_control_text([
                'name' => $field_attrs['name'],
                'type' => 'text',
                'id'   => $input_id,
                'std'  => '',
                'attr' => $field_attrs['attr'],
            ]);

            // $script = "$('.change-image').on('click', function (e){\n"
            //         . "    $('#".$image_id."').attr('src', '".$captcha_url."?'+Math.random());\n"
            //         . "    $('#".$input_id."').focus();\n"
            //         . "    e.preventDefault();\n"
            //         . "});";

            // load_script('collcaptha-trigger', $script);

            if (!extension_loaded('gd')) {
                $field_attrs['class'] = ' has-error';
                $output = '<p class="form-control form-control-static">'.lang('biform_gdext_notfound').'</p>';
            }
        }

        return $output;
    }

    /**
     * Bootstrap Switch
     *
     * @param   array  $field_attrs  Field Attributes
     * @return  string
     */
    protected function _control_switch(array $field_attrs)
    {
        // load_script('bs-switch');

        if (!isset($field_attrs['option'])) {
            $field_attrs['option'] = ['Off', 'On'];
        }

        if (count($field_attrs['option']) > 2) {
            return '<span class="form-control form-control-static">Pilihan tidak boleh lebih dari 2 (dua)!!</span>';
        }

        $_id = str_replace('-', '-', $field_attrs['name']);
        $field_attrs['std'] = (int) $field_attrs['std'];
        $checked = ($field_attrs['std'] == 1 ? true : false);

        return form_checkbox([
            'name'          => $field_attrs['name'],
            'id'            => $_id,
            'class'         => 'bs-switch',
            'value'         => 1,
            'checked'       => set_checkbox($field_attrs['name'], 1, $checked),
            'data-off-text' => $field_attrs['option'][0],
            'data-on-text'  => $field_attrs['option'][1],
        ]);
    }

    /**
     * Dropdown and Multiselect Form
     *
     * @param   array  $field_attrs  Field Attributes
     * @return  string
     */
    protected function _control_selectbox(array $field_attrs)
    {
        $native = (isset($field_attrs['native']) ? $field_attrs['native'] : false);
        $control_class = '';

        if ($field_attrs['type'] == 'multiselect') $field_attrs['name'] = $field_attrs['name'].'[]';
        if ($field_attrs['type'] == 'select2') {
            $field_attrs['type'] = 'dropdown';
            $native = false;
        }

        if ($native == false) {
            // load_script('select2-'.get_lang_code());
            $control_class = 'form-control-select2 ';
        }

        $field_attrs['attr'] = 'class="'.$control_class.$this->_template['field_class'].'" id="'.$field_attrs['id'].'" '.$field_attrs['attr'];
        $form_func = 'form_'.$field_attrs['type'];

        if ( !is_array_assoc($field_attrs['option']) ) {
            $_tmp = $field_attrs['option'];
            $field_attrs['option'] = [];

            foreach ($_tmp as $opt) {
                $field_attrs['option'][$opt] = $opt;
            }
        }

        return call_user_func_array($form_func, array(
            $field_attrs['name'],
            $field_attrs['option'],
            set_value($field_attrs['name'], $field_attrs['std']),
            $field_attrs['attr']
        ));
    }

    /**
     * FineUploader Form
     *
     * @param   array  $field_attrs  Field Attributes
     * @return  string
     */
    protected function _control_upload(array $field_attrs)
    {
        $field_attrs = array_set_defaults($field_attrs, array(
            'allowed_types' => '',
            'file_limit'    => 5,
        ));

        if (is_array($field_attrs['allowed_types'])) {
            $field_attrs['allowed_types'] = implode('|', $field_attrs['allowed_types']);
        }

        $uploader = $this->CI->biupload->initialize(array(
            'allowed_types' => $field_attrs['allowed_types'],
            'file_limit'    => $field_attrs['file_limit'],
        ));

        $desc = $uploader->upload_policy();

        if (isset($field_attrs['desc'])) {
            $field_attrs['desc'] .= '. '.$desc;
        } else {
            $field_attrs['desc'] = $desc;
        }

        return $uploader->get_html($field_attrs['name']);
    }

    /**
     * Default CI Text Form
     *
     * @param   array  $field_attrs  Field Attributes
     * @return  string
     */
    protected function _control_datepicker(array $field_attrs)
    {
        $lang = get_lang_code();

        if (!isset($field_attrs['mode'])) {
            $field_attrs['mode'] = 'bootstrap';
        }

        if ($field_attrs['mode'] == 'jqueryui') {
            // load_script('jqueryui-datepicker-'.$lang);
        } elseif ($field_attrs['mode'] == 'bootstrap') {
            // load_script('bs-datepicker-'.$lang);
        }

        $field_attrs['class'] = 'form-datepicker';
        $field_attrs['type'] = 'text';
        $field_attrs['callback'] = 'string_to_date';
        $field_attrs['attr'] = 'data-lang="'.$lang.'" data-mode="'.$field_attrs['mode'].'" data-format="dd-mm-yyyy" ';
        $field_attrs['std'] = bdate('%d-%m-%Y', $field_attrs['std']);

        $output = '<div class="has-feedback">'
                    . $this->_control_text($field_attrs)
                . '<span class="fa fa-calendar form-control-feedback"></span></div>';

        return $output;
    }

    /**
     * Static Form
     * @link    http://getbootstrap.com/css/#forms-controls-static
     *
     * @param   array  $field_attrs  Field Attributes
     * @return  string
     */
    protected function _control_static(array $field_attrs)
    {
        $attributes = parse_html_attrs([
            'id'    => $field_attrs['id'],
            'class' => str_replace('form-control', 'form-control-static', $this->_template['field_class']),
        ]);

        return '<p '.$attributes.'>'.$field_attrs['std'].'</p>';
    }

    /**
     * Validate submission, it will setup validation rules of each field
     * using default CI Form Validation
     *
     * @return  bool
     */
    public function validate_submition()
    {
        foreach ($this->_fields as $name => $field) {
            if (!isset($field['name']) and is_string($name) and strlen($name) > 0) {
                $field['name'] = $name;
            }

            if ($field['type'] == 'subfield') {
                foreach ($field['fields'] as $sf_name => $sub_field) {
                    if (!isset($sub_field['name']) and is_string($sf_name) and strlen($sf_name) > 0) {
                        $sub_field['name'] = $sf_name;
                    }

                    $sub_validation = isset($sub_field['validation']) ? $sub_field['validation'] : '';
                    $sub_callback   = isset($sub_field['callback'])   ? $sub_field['callback']   : '';

                    $this->set_rules($field['name'].'_'.$sub_field['name'], $sub_field['label'], $sub_field['type'], $sub_validation, $sub_callback);
                }
            } elseif ($field['type'] == 'rangeslider') {
                $validation = (isset($field['validation']) ? $field['validation'] : '');
                $callback   = (isset($field['callback'])   ? $field['callback']   : '');

                $this->set_rules($field['name'].'_min', $field['label'], $field['type'], $validation, $callback);
                $this->set_rules($field['name'].'_max', $field['label'], $field['type'], $validation, $callback);
            } elseif ($field['type'] != 'static' AND $field['type'] != 'fieldset') {
                $validation = (isset($field['validation']) ? $field['validation'] : '');
                $callback   = (isset($field['callback'])   ? $field['callback']   : '');

                if ($field['type'] == 'editor') {
                    $filters = isset($field['filters']) ? $field['filters'] : [];
                    $defaults = array(
                        '<?php echo' => '{%=',
                        '<?php' => '{%',
                        '?>' => '%}',
                    );

                    if (!empty($filters)) {
                        $defaults = array_merge($filters, $defaults);
                    }

                    $this->_template['editor_filters'][$field['name']] = $defaults;
                }

                $this->set_rules($field['name'], $field['label'], $field['type'], $validation, $callback);
            }
        }

        // if is valid submissions
        if ($this->CI->form_validation->run()) {
            foreach ($this->_fields as $field) {
                $name = $field['name'];
                if ($field['type'] == 'editor' and isset($this->_template['editor_filters'][$name])) {
                    $replacements = $patterns = [];
                    $filters = array_merge($this->_template['editor_filters'][$name], array(
                        '&nbsp;' => ' '
                    ));

                    foreach ($this->_template['editor_filters'][$name] as $replacement => $pattern) {
                        $replacements[] = $replacement;
                        $patterns[] = $pattern;
                    }

                    $this->form_data[$name] = str_replace($patterns, $replacements, $this->form_data[$name]);
                } elseif ($field['type'] == 'datepicker') {
                    $this->form_data[$name] = string_to_date($this->form_data[$name]);
                }
            }

            $data = $this->form_data;
            $this->clear();
            return $data;
        }

        // otherwise
        return false;
    }

    /**
     * Setup field validation rules
     *
     * @return  void
     */
    protected function set_rules($name, $label, $type, $validation = '', $callback = '')
    {
        $field_arr = (strpos($name, '[]') === false OR $type == 'checkbox' OR $type == 'multiselect' OR $type == 'upload' ? true : false);
        $rules     = ($field_arr ? 'xss_clean' : 'trim|xss_clean');

        if (strlen($validation) > 0) {
            $rules .= '|'.$validation;
        }

        $this->CI->form_validation->set_rules($name, $label, $rules);

        $method = $this->_attrs['method'];

        if (strlen($callback) > 0 and function_exists($callback) and is_callable($callback)) {
            $this->form_data[$name] = call_user_func($callback, $this->CI->input->$method($name));
        } else {
            $this->form_data[$name] = $this->CI->input->$method($name);
        }

        if (isset($this->_attrs['hiddens'])) {
            foreach ($this->_attrs['hiddens'] as $h_name => $h_value) {
                $this->form_data[$h_name] = $this->CI->input->$method($h_name);
            }
        }

        if ($type == 'upload') {
            $files = $this->form_data[$name];

            if (count($files) == 1) {
                $this->form_data[$name] = $files[0];
            }
        }
    }

    /**
     * Field description
     *
     * @param   string|array  $desc  Description about what this field is
     * @return  string
     */
    protected function _form_desc($desc = NULL)
    {
        $ret = '';

        if (is_null($desc)) {
            $ret = '';
        } elseif (is_string($desc) and strlen($desc) > 0) {
            $ret = $this->_template['desc_open'].$desc.$this->_template['desc_close'];
        } elseif (is_array($desc) and !empty($desc)) {
            $descs = isset($desc['err']) ? $desc['err'] : $desc;

            foreach ($descs as $ket) {
                $ret .= $ket;
            }
        }

        return $ret;
    }

    /**
     * Form action buttons
     *
     * @return  string
     */
    protected function _form_actions()
    {
        // If you have no buttons i'll give you two as default ;)
        // 1. Submit button as Bootstrap btn-primary on the left side
        // 2. Reset button as Bootstrap btn-default on the right side
        if (count($this->_buttons) == 0) {
            $this->_buttons[] = array(
                'name'  => 'submit',
                'type'  => 'submit',
                'label' => 'lang:button_submit',
                'class' => 'pull-left btn-primary'
            );
            $this->_buttons[] = array(
                'name'  => 'reset',
                'type'  => 'reset',
                'label' => 'lang:button_reset',
                'class' => 'pull-right btn-default'
            );
        }

        // If you were use Bootstrap form-horizontal class in your form,
        // You'll need to specify Bootstrap grids class.
        $group_col  = $this->is_hform ? 'col-sm-12' : '';
        $html       = '<div class="form-group form-action"><div class="clearfix '.$group_col.'">';

        // Let's reset your button attributes.
        $button_attr = [];

        foreach ($this->_buttons as $attr) {
            // Button name is inherited with form ID.
            $button_attr['name']  = $this->_attrs['name'].'-'.$attr['name'];
            // If you not specify your Button ID, you'll get it from Button name with '-btn' as surfix.
            $button_attr['id']    = (isset($attr['id']) ? $attr['id'] : $button_attr['name']).'-btn';
            // I prefer to use Bootstrap btn-sm as default.
            $button_attr['class'] = $this->_template['buttons_class'].(isset($attr['class']) ? ' '.$attr['class'] : '');

            if (substr($attr['label'], 0, 5) == 'lang:') {
                $attr['label'] = lang(str_replace('lang:', '', $attr['label']));
            }

            if (isset($attr['disabled']) && $attr['disabled'] === true) {
                $button_attr['disabled'] = 'disabled';
            }

            // $button_attr['data-loading-text']  = 'Loading...';
            // $button_attr['data-complete-text'] = 'Finished!';

            switch ($attr['type']) {
                // For submit and reset type input
                // <input type="[submit|reset]" ...>
                case 'submit':
                case 'reset':
                    $func = 'form_'.$attr['type'];
                    $button_attr['value'] = $attr['label'];
                    $html .= $func($button_attr);
                    break;

                // For button type button
                // <button type="button" ...></button>
                case 'button':
                    $button_attr['content'] = $attr['label'];
                    $html .= form_button($button_attr);
                    break;

                // For anchor type button
                // <a href="url" class="btn ..." ...></a>
                case 'anchor':
                    $attr['url'] = (isset($attr['url']) AND strlen($attr['url']) > 0) ? $attr['url'] : '#';
                    $html .= anchor(($attr['url'] != '#' ? $attr['url'] : current_url().'#'), $attr['label'], $button_attr);
                    break;
            }
        }

        $html .= '</div></div>';

        return $html;
    }

    /**
     * Return the submited data
     *
     * @return  array
     */
    public function submited_data()
    {
        $data = $this->form_data;
        $this->clear();

        return $data;
    }

    /**
     * Clean up form properties
     * It's useful if you have multiple form declarations.
     *
     * @return  void
     */
    public function clear()
    {
        $this->_attrs = [
            'action'  => null,
            'name'    => null,
            'id'      => null,
            'class'   => null,
            'method'  => 'post',
            'extras'  => [],
            'hiddens' => [],
        ];

        $this->is_hform     = false;
        $this->is_multipart = false;
        $this->no_buttons   = false;
        $this->has_fieldset = false;
        $this->_fields      = [];
        $this->_buttons     = [];
        $this->_errors      = [];
    }
}
