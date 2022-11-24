-------------------- spu_mantencion_sw---------------------------------
alter PROCEDURE spu_mantencion_sw(@ve_operacion				varchar(20)
									,@ve_cod_mantencion_sw		numeric
	    							,@ve_fecha_mantencion_sw	datetime = null
	    							,@ve_cod_usuario			numeric = null
						    		,@ve_cod_usuario_solicita	numeric = null
	    							,@ve_referencia				varchar(100) = null
	    							,@ve_descripcion			text = null
	    							,@ve_es_garantia			varchar(1) = null
	    							,@ve_cod_item_menu			varchar(10) = null
	    							,@ve_autoriza				varchar(1) = null
	    							,@ve_cod_usuario_autoriza	numeric = null
	    							,@ve_fecha_autoriza			datetime = null)	 
	    							
AS
BEGIN
	
	declare @vl_cod_mantencion	numeric
	
	if (@ve_operacion='INSERT')
		insert into mantencion_sw 
			(fecha_mantencion_sw
	    	,cod_usuario
			,cod_usuario_solicita
	    	,referencia
	    	,descripcion
	    	,es_garantia
	    	,cod_item_menu
	    	,autoriza
	    	,cod_usuario_autoriza
	    	,fecha_autoriza)
		values 
			(@ve_fecha_mantencion_sw
	    	,@ve_cod_usuario
			,@ve_cod_usuario_solicita
	    	,@ve_referencia
	    	,@ve_descripcion
	    	,@ve_es_garantia
	    	,@ve_cod_item_menu
	    	,@ve_autoriza
	    	,@ve_cod_usuario_autoriza
	    	,@ve_fecha_autoriza)

	else if (@ve_operacion='UPDATE')
		update mantencion_sw
		set fecha_mantencion_sw = @ve_fecha_mantencion_sw
	    	,cod_usuario = @ve_cod_usuario
			,cod_usuario_solicita = @ve_cod_usuario_solicita
	    	,referencia = @ve_referencia
	    	,descripcion = @ve_descripcion
	    	,es_garantia = @ve_es_garantia
	    	,cod_item_menu = @ve_cod_item_menu
	    	,autoriza = @ve_autoriza
	    	,cod_usuario_autoriza = @ve_cod_usuario_autoriza
	    	,fecha_autoriza = @ve_fecha_autoriza
		where cod_mantencion_sw = @ve_cod_mantencion_sw
	else if (@ve_operacion='DELETE')
		delete mantencion_sw  
		where cod_mantencion_sw = @ve_cod_mantencion_sw
END
go
