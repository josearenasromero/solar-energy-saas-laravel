<?php

namespace Modules\AlsoEnergy\Resources;

use Modules\Common\Resources\GenericResource;
use Modules\QOS\Resources\PlantResource;

class AESiteResource extends GenericResource
{
    private $detailed;
    public function __construct($resource, $detailed = true)
    {
        parent::__construct($resource);
        $this->detailed = $detailed;
    }

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'ae_site_id'=> $this->ae_site_id,
            'name' => $this->name,
            'timezone' => $this->timezone,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address1' => $this->address1,
            'zip_code' => $this->zip_code,      
            'city' => $this->city,
            'state'=>$this->state,
            'country'=>$this->country,
            'turn_on_date'=>$this->turn_on_date,
            'plants' =>(isset($this->plant) && $this->detailed)? (new PlantResource($this->plant,false)):null,
        ];
    }

    
}
