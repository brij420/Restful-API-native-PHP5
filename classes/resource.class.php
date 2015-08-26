<?php

class Resource extends API {

    function __construct($request) {
        parent::__construct($request);
    }

    function _response($resource = NULL) {
        $this->response = $resource->{mb_strtolower($this->request['action'])}();
        if (!empty($this->response) && ($this->response)) {//"self" => $this->uri, 
            return $this->responseAPI(array("data" => $this->response), 200);
        }
        return $this->responseAPI(array("self" => $this->uri, "data" => "Invalid data"), 204);
    }

    function resourceResponse() {
        switch (mb_strtolower($this->resource)) {
            case "profile":
                $this->resource_obj = new Profile($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "user":
                $this->resource_obj = new User($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "groups":
                $this->resource_obj = new Groups($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "schools":
                $this->resource_obj = new Schools($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "businessdeals":
                $this->resource_obj = new BusinessDeals($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "business":
                $this->resource_obj = new Business($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "deals":
                $this->resource_obj = new Deals($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "posts":
                $this->resource_obj = new Posts($this->request);
                return $this->_response($this->resource_obj);
                break;


            case "content":
                $this->resource_obj = new Content($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "raffle":
                $this->resource_obj = new Raffle($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "session":
                $this->resource_obj = new Session($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "raffletickets":
                $this->resource_obj = new RaffleTickets($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "userblock":
                $this->resource_obj = new UserBlock($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "userdealdetails":
                $this->resource_obj = new UserDealDetails($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "userfollow":
                $this->resource_obj = new UserFollow($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "userschoolmap":
                $this->resource_obj = new UserSchoolMap($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "bizcategorymapping":
                $this->resource_obj = new BizCategoryMapping($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "bizdatasource":
                $this->resource_obj = new BizDataSource($this->request);
                return $this->_response($this->resource_obj);
                break;
            case "bizhours":
                $this->resource_obj = new BizHours($this->request);
                return $this->_response($this->resource_obj);
                break;
            case "bizschoolmap":
                $this->resource_obj = new BizSchoolMap($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "categories":
                $this->resource_obj = new Categories($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "chainid":
                $this->resource_obj = new ChainId($this->request);
                return $this->_response($this->resource_obj);
                break;
            case "contentmetadata":
                $this->resource_obj = new SIDT_ContentMetadata($this->request);
                return $this->_response($this->resource_obj);
                break;
            case "cronhistory":
                $this->resource_obj = new CronHistory($this->request);
                return $this->_response($this->resource_obj);
                break;
            case "groupmembers":
                $this->resource_obj = new GroupMembers($this->request);
                return $this->_response($this->resource_obj);
                break;
            case "layout":
                $this->resource_obj = new Layout($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "ticksheet":
                $this->resource_obj = new TickSheet($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "password":
                $this->resource_obj = new Password($this->request, $this->resource);
                return $this->_response($this->resource_obj);
                break;

            case "businessimage":
                $this->resource_obj = new BusinessImage($this->request);
                return $this->_response($this->resource_obj);
                break;
            case "imageupload":
                $this->resource_obj = new ImageUpload($this->request);
                return $this->_response($this->resource_obj);
                break;

            case "discoverenginecategory":
                $this->resource_obj = new DiscoverEngineCategory($this->request);
                return $this->_response($this->resource_obj);
                break;
        }
        return $this->responseAPI(array("data" => array("message" => "Invalid resource", "error" => "999")), 204);
    }

}
