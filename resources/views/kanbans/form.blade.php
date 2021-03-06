@csrf
@include ('forms.input.text', 
                    ["model" => "kanban", 
                    "field" => "title", 
                    "placeholder" => trans('global.kanban.fields.title'),  
                    "required" => true, 
                    "value" => old('title', isset($kanban) ? $kanban->title : '')])

@include ('forms.input.textarea', 
                    ["model" => "kanban", 
                    "field" => "description", 
                    "placeholder" => trans('global.kanban.fields.description'),  
                    "rows" => 3, 
                    "value" => old('description', isset($logbook) ? $kanban->description : '')])                                                                                                                          

<div>
    <input 
        id="logbook-save"
        class="btn btn-info" 
        type="submit" 
        value="{{ $buttonText }}">
</div>