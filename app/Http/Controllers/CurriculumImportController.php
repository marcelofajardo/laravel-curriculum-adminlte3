<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Curriculum;
use App\Grade;
use App\Subject;
use App\OrganizationType;
use App\TerminalObjective;
use App\EnablingObjective;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use DOMDocument;
use App\Content;
use App\Glossar;
use App\CurriculumImport;
use Illuminate\Support\Facades\Storage;
use App\Medium;
use App\Reference;
use Illuminate\Support\Facades\File;
use App\Quote;
use App\QuoteSubscription;

class CurriculumImportController extends Controller
{
    
    public function import()
    {
        return view('curricula.import');
        
    }
    
    /*
     * example http://127.0.0.1:8000/curricula/import?path=/curricula/2019-07-24_15-43-14_curriculum_nr_347.curriculum
     */
    public function store() {
        $folder = 'imports';
        
        if (!request()->hasFile('import'))
        {
            return redirect('/home');
        }
        $original_file_name = request()->file("import")->getClientOriginalName();
        $zip_path = request()
                ->file('import')
                ->storeAs("/{$folder}", $original_file_name
                );
        
        \Zipper::make(storage_path("app/{$folder}/").request()->file("import")->getClientOriginalName())->extractTo(storage_path("app/{$folder}/"));
        
        $filename = pathinfo(request()->file("import")->getClientOriginalName(), PATHINFO_FILENAME);
        
        $xml = new DOMDocument("1.0", "UTF-8");
        //dd(public_path("{$folder}/{$filename}.xml"));
        $xml->load(storage_path("app/{$folder}/{$filename}.xml"));
        $xml_data = $xml->getElementsByTagName('curriculum')[0];
        $old_curriculum_id = $xml_data->getAttribute('id');
        $curriculum_import_data = [
            'title' => $xml_data->getAttribute('curriculum'),
            'description' => html_entity_decode($xml_data->getAttribute('description')),
            'grade_id' => optional(Grade::where("title", $xml_data->getAttribute('grade'))->first())->id ?: 1,
            'subject_id' => optional(Subject::where("title", $xml_data->getAttribute('subject'))->first())->id ?: 1,
            'organization_type_id' => optional(OrganizationType::where("external_id", $xml_data->getAttribute('schooltype'))->first())->external_id ?: 1,
            'state_id' => "DE-RP",
            'country_id' => "DE",
            'medium_id' => null,
            'owner_id' => auth()->user()->id
        ];

        //persist curriculum
        $curriculum = Curriculum::create($curriculum_import_data);


        //persist terminal_objectives
        foreach ($xml->getElementsByTagName('terminal_objective') as $ter) {
            $old_ter_id = $ter->getAttribute('id');
            $terminal_import_data = [
                'curriculum_id' => $curriculum->id,
                'title' => html_entity_decode($ter->getAttribute('terminal_objective')),
                'description' => html_entity_decode($ter->getAttribute('description')),
                'order_id' => $ter->getAttribute('order_id'),
                'color' => $ter->getAttribute('color'),
                'objective_type_id' => $ter->getAttribute('type_id'),
                    //perist references, ter_files
            ];
            //dd($terminal_import_data);
            $terminal_objective = TerminalObjective::create($terminal_import_data);
            
            /* ter files */
            $terminal_media_nodes = getImmediateChildrenByTagName($ter, 'file');
            foreach($terminal_media_nodes as $terminal_medium) {
                $this->importMedia($terminal_medium, $terminal_objective, $folder, $old_curriculum_id.'/'.$old_ter_id.'/'); //call import function
            }
            
            /*ter references*/
            $this->process($ter, $terminal_objective, 'reference', 'importReference');
            $this->process($ter, $terminal_objective, 'quote_subscription', 'importQuoteSubscription');
            
            //persist enabing_objectives  
            foreach ($ter->getElementsByTagName('enabling_objective') as $ena) {
                $old_ena_id = $ena->getAttribute('id');
                $enabling_import_data = [
                    'curriculum_id' => $curriculum->id,
                    'terminal_objective_id' => $terminal_objective->id,
                    'title' => htmlspecialchars_decode($ena->getAttribute('enabling_objective'), ENT_QUOTES),
                    'description' => htmlspecialchars_decode($ena->getAttribute('description'), ENT_QUOTES),
                    'order_id' => $ter->getAttribute('order_id'),
                        //perist references, ena_files
                ];
                //dd($terminal_import_data);
                $enabling_objective = EnablingObjective::create($enabling_import_data);
                
                /* ena files */
                $enabling_medium_nodes = getImmediateChildrenByTagName($ena, 'file');
                foreach($enabling_medium_nodes as $enabling_medium) {
                    $this->importMedia($enabling_medium, $enabling_objective, $folder, $old_curriculum_id.'/'.$old_ter_id.'/'.$old_ena_id.'/'); //call import function
                }
                
                $this->process($ena, $enabling_objective, 'reference', 'importReference');
                $this->process($ena, $enabling_objective, 'quote_subscription', 'importQuoteSubscription');
                
            }
        }
        
        /* import content */
        $this->process($xml_data, $curriculum, 'content', 'importContent');

        /* end import content */
        
        /* import glossar */
        $glossar_content_nodes = getImmediateChildrenByTagName($xml_data, 'glossar');
        if (count($glossar_content_nodes) > 0)
        {
            $glossar = new Glossar([
                "subscribable_type" => get_class($curriculum),
                "subscribable_id" =>  $curriculum->id,
            ]);
            $glossar->save();
            $glossar->fresh();
            foreach ($glossar_content_nodes as $gl) {
                $this->importContent($gl, $glossar);
            }
        }
        /* end import glossar */
        
        
        /* import curriculum files */
        $media_content_nodes = getImmediateChildrenByTagName($xml_data, 'file');
        foreach($media_content_nodes as $cur_fil) {
            $this->importMedia($cur_fil, $curriculum, $folder, $old_curriculum_id.'/'); 
        }
        /* end import curriculum files */
        
        /**
         * clean up
         */
        Storage::deleteDirectory($folder);
        
        
        
        return redirect('/curricula/'.$curriculum->id);
    }
    
    private function importContent($content_node, $model){
        $content = new Content([
            "title" =>  htmlspecialchars_decode(getImmediateChildrenByTagName($content_node, 'title')[0]->nodeValue, ENT_QUOTES),
            "content" =>  htmlspecialchars_decode(getImmediateChildrenByTagName($content_node, 'text')[0]->nodeValue, ENT_QUOTES), 
            "owner_id"=> auth()->user()->id,
        ]);
        $content->save();
        $content->fresh();
        
        //import possible quotes
        foreach(getImmediateChildrenByTagName($content_node, 'quote') as $quote_node) {
            $this->importQuote($quote_node, $content);
        }
        
       
        $content->subscribe($model);
        
    }
    
    private function importMedia($media_node, $model, $folder ,$path){
        $new_folder = class_basename($model);
        $temp_filepath = storage_path("app/{$folder}/{$path}").$media_node->getAttribute('filename');
        if (!file_exists($temp_filepath)) {
            dump('missing'.$temp_filepath);
            return;
        }
        $media = new Medium([
            'path'          => "/{$new_folder}/{$model->id}/",
            'title'         => $media_node->getAttribute('title'),
            'medium_name'   => $media_node->getAttribute('filename'),
            'description'   => $media_node->getAttribute('description'),
            'author'        => $media_node->getAttribute('author'),
            'publisher'     => $media_node->getAttribute('publisher'),
            'city'          => $media_node->getAttribute('city'),
            'date'          => $media_node->getAttribute('date'),
            'size'          => File::size($temp_filepath),
            'mime_type'     => File::mimeType($temp_filepath),
            'license_id'    => $media_node->getAttribute('license'),
           
            'owner_id'      => auth()->user()->id,
            
        ]);
        $media->save();
        
        Storage::disk('local')
                ->move("{$folder}/{$path}".$media_node->getAttribute('filename'),
                       "/{$new_folder}/{$model->id}/{$media_node->getAttribute('filename')}"
                );

        $media->fresh();
       
        $media->subscribe($model);
        
    }
    
    public function process($base_node, $model, $tag, $function)
    {
        $nodes = getImmediateChildrenByTagName($base_node, $tag);
        foreach($nodes as $ref) {
            $this->$function($ref, $model); //call import function
        }
    }
    
    private function importReference($ref_node, $model)
    {
        
       $reference = Reference::where('id', $ref_node->getAttribute('unique_id'))->first(); //check first --> do not use firstOrCreate, it will fail on some older *.curriculum exports
       
        if ($reference === null) {
            $reference = Reference::Create(["id"            => $ref_node->getAttribute('unique_id'),
                                            "owner_id"      => auth()->user()->id,
                                            "description"   => htmlspecialchars_decode( $this->getDescription($ref_node), ENT_QUOTES),
                                            'grade_id'      => optional(Grade::where("title", $ref_node->getAttribute('grade'))->first())->id ?: 1,
                                         ]);
        }
        
         
        if ($reference->id == 0){
           $reference = Reference::find($ref_node->getAttribute('unique_id'));
        }
        
        $reference->subscribe($model);
        
    }
    private function getDescription($ref_node)
    {   
        if (!isset(getImmediateChildrenByTagName($ref_node, 'content')[0])) //fix bug in import files
        {
            return null;
        }
        $node = getImmediateChildrenByTagName($ref_node, 'content')[0];
        return getImmediateChildrenByTagName($node, 'text')[0]->nodeValue;
    }
    
    private function importQuote($ref_node, $model)
    {
        if (Quote::where('id', $ref_node->getAttribute('unique_id'))->first() === null)
        {
            $content = Content::where('id', $model->id)->get();
           
            $regex   = '/\<quote id="quote_'.$ref_node->getAttribute('unique_id').'"\>(|.+?)\<\/quote\>/s';
            preg_match($regex, $content->first()->content, $matches);
           
            Quote::firstOrCreate(["id"       => $ref_node->getAttribute('unique_id'),
                              "referenceable_type"=> get_class($model),
                              "referenceable_id"=> $model->id,
                              "quote" =>  $matches ? $matches[1] : null,//$matches[0] == with quote tag //$matches[1] == quote only
                              "owner_id" => auth()->user()->id,
                             ]);
            
        }
    }
    
    private function importQuoteSubscription($ref_node, $model)
    {
   
        QuoteSubscription::firstOrCreate([
			"quote_id" =>  $ref_node->getAttribute('unique_id'),
			"quotable_type"=> get_class($model),
			"quotable_id"=> $model->id,
			"sharing_level_id"=> $ref_node->getAttribute('sharing_level_id'),
			"visibility"=> $ref_node->getAttribute('sharing_level_id'),
			"owner_id"=> auth()->user()->id,	
	]);
    }
    
  
}