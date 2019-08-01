<?php

/**
* @OA\Get(
*      path="/v1/organizations",
*      operationId="organizations",
*      tags={"Organization v1"},
*      summary="Get all organizations",
*      description="Returns a collection of organization objects",
*      security={
*           {"passport": {"*"}},
*      },
*      @OA\Response(
*          response=200,
*          description="successful operation",
*          @OA\Schema(ref="#/components/schemas/Organizations"),
*       ),
*       @OA\Response(response=400, description="Bad request"),
* )
*
*/

/**
* @OA\POST(
*      path="/v1/organizations",
*      operationId="organizations",
*      tags={"Organization v1"},
*      summary="Create new organization",
*      description="Returns a the new organization object",
*      security={
*           {"passport": {"*"}},
*      },
*      @OA\RequestBody(
*       required=true,
*       @OA\MediaType(
*           mediaType="application/x-www-form-urlencoded",
*           @OA\Schema(
*               type="object",
*               required={"title", "common_name", "organization_type_id", "state_id", "country_id", "status_id"},
*               @OA\Property(
*                   property="title",
*                   description="Organization title",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="common_name",
*                   description="Common Name for this Organization",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="organization_type_id",
*                   description="Organization Type Id",
*                   type="integer",
*               ),
*               @OA\Property(
*                   property="state_id",
*                   description="State Id",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="country_id",
*                   description="Country Id",
*                   type="string",
*               ),
*               @OA\Property(
*                   property="status_id",
*                   description="Status",
*                   type="integer",
*               ),
*               @OA\Property(
*                   property="description",
*                   description="Description",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="street",
*                   description="Street",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="postcode",
*                   description="Postcode",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="city",
*                   description="City",
*                   type="string"
*               ),
*              
*               @OA\Property(
*                   property="phone",
*                   description="Phone",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="email",
*                   description="Email",
*                   type="string"
*               ),
*           )
*       )
*   ),
*      
*      @OA\Response(
*          response=200,
*          description="successful operation",
*          @OA\JsonContent(ref="#/components/schemas/Organization")   
*       ),
*       @OA\Response(response=400, description="Bad request"),
*       security={
*           {"api_key_security_example": {}}
*       }
*      
* )
*/

/**
* @OA\PUT(
*      path="/v1/organizations/{id}",
*      operationId="organizations",
*      tags={"Organization v1"},
*      summary="Edit organization",
*      description="Edit organization",
*      security={
*           {"passport": {"*"}},
*      },
*      @OA\Parameter(
*          name="id",
*          description="Organization id",
*          required=true,
*          in="path",
*          @OA\Schema(
*              type="integer"
*          )
*      ),
*      @OA\RequestBody(
*       required=true,
*       @OA\MediaType(
*           mediaType="application/x-www-form-urlencoded",
*           @OA\Schema(
*               type="object",
*               @OA\Property(
*                   property="title",
*                   description="Organization title",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="common_name",
*                   description="Common Name for this Organization",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="organization_type_id",
*                   description="Organization Type Id",
*                   type="integer",
*               ),
*               @OA\Property(
*                   property="state_id",
*                   description="State Id",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="country_id",
*                   description="Country Id",
*                   type="string",
*               ),
*               @OA\Property(
*                   property="description",
*                   description="Description",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="street",
*                   description="Street",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="postcode",
*                   description="Postcode",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="city",
*                   description="City",
*                   type="string"
*               ),
*              
*               @OA\Property(
*                   property="phone",
*                   description="Phone",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="email",
*                   description="Email",
*                   type="string"
*               ),
*               @OA\Property(
*                   property="status_id",
*                   description="Status",
*                   type="integer",
*               ),
*           )
*       )
*   ),
*      
*       @OA\Response(
*          response=200,
*          description="successful operation",
*          @OA\JsonContent(ref="#/components/schemas/Organization")   
*       ),
*       @OA\Response(response=400, description="Bad request"),
*       security={
*           {"api_key_security_example": {}}
*       }
*      
* )
* 
*/

/**
* @OA\GET(
*      path="/v1/organizations/{id}",
*      operationId="organizations",
*      tags={"Organization v1"},
*      summary="Get organization by Id",
*      description="Returns a organization object",
*      security={
*           {"passport": {"*"}},
*      },
*      @OA\Parameter(
*          name="id",
*          description="Organization id",
*          required=true,
*          in="path",
*          @OA\Schema(
*              type="integer"
*          )
*      ),
*      
*      @OA\Response(
*          response=200,
*          description="successful operation",   
*            @OA\JsonContent(ref="#/components/schemas/Organization")   
*         ),
*       ),
*       @OA\Response(response=400, description="Bad request"),
*       security={
*           {"api_key_security_example": {}}
*       }
*      
* )
* 
*/

/**
* @OA\DELETE(
*      path="/v1/organizations/{id}",
*      operationId="organizations",
*      tags={"Organization v1"},
*      summary="Delete organization by Id",
*      description="Delete a organization object",
*      security={
*           {"passport": {"*"}},
*      },
*      @OA\Parameter(
*          name="id",
*          description="Organization id",
*          required=true,
*          in="path",
*          @OA\Schema(
*              type="integer"
*          )
*      ),
*      
*      @OA\Response(
*          response=200,
*          description="successful operation",   
*       ),
*       @OA\Response(response=400, description="Bad request"),
*      
* )
* 
*/

/**
* @OA\PUT(
*      path="/v1/organizations/enrol",
*      operationId="organizations",
*      tags={"Organization v1"},
*      summary="Create organization enrolment",
*      description="Create organization enrolment",
*      security={
*           {"passport": {"*"}},
*      },
*      @OA\RequestBody(
*       required=true,
*       @OA\MediaType(
*           mediaType="application/x-www-form-urlencoded",
*           @OA\Schema(
*               type="object",
*               @OA\Property(
*                   property="user_id",
*                   description="User Id",
*                   type="integer"
*               ),
*               @OA\Property(
*                   property="organization_id",
*                   description="Organization Id",
*                   type="integer"
*               ),
*               @OA\Property(
*                   property="role_id",
*                   description="Role Id",
*                   type="integer",
*               ),
*           )
*       )
*   ),
*      
*       @OA\Response(
*          response=201,
*          description="Created enrolment",
*          @OA\JsonContent(ref="#/components/schemas/OrganizationRoleUser")   
*       ),
*       @OA\Response(response=400, description="Bad request"),
*       security={
*           {"api_key_security_example": {}}
*       }
*      
* )
* 
*/

/**
* @OA\DELETE(
*      path="/v1/organizations/expel",
*      operationId="organizations",
*      tags={"Organization v1"},
*      summary="Delete organization enrolment",
*      description="Delete organization enrolment",
*      security={
*           {"passport": {"*"}},
*      },
*      @OA\RequestBody(
*       required=true,
*       @OA\MediaType(
*           mediaType="application/x-www-form-urlencoded",
*           @OA\Schema(
*               type="object",
*               @OA\Property(
*                   property="user_id",
*                   description="User Id",
*                   type="integer"
*               ),
*               @OA\Property(
*                   property="organization_id",
*                   description="Organization Id",
*                   type="integer"
*               ),
*           )
*       )
*   ),
*      
*       @OA\Response(
*          response=200,
*          description="successful operation",
*       ),
*       @OA\Response(response=400, description="Bad request"),
*       security={
*           {"api_key_security_example": {}}
*       }
*      
* )
* 
*/

