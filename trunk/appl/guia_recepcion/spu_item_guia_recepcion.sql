-------------------- spu_item_guia_recepcion---------------------------------
CREATE PROCEDURE [dbo].[spu_item_guia_recepcion](
					@ve_operacion					varchar(20)
					,@ve_cod_item_guia_recepcion	numeric
					,@ve_cod_guia_recepcion			numeric		
					,@ve_cod_producto				varchar(30)	=NULL
					,@ve_nom_producto				varchar(100)=NULL
					,@ve_cantidad					T_CANTIDAD	=NULL
					,@ve_cod_item_doc				numeric		=NULL
					,@ve_tipo_doc					varchar(30)=NULL)

AS
BEGIN
	if (@ve_operacion='INSERT') 
		begin
			insert into item_guia_recepcion(
				cod_guia_recepcion
				,cod_producto
				,nom_producto
				,cantidad
				,cod_item_doc
				,tipo_doc)
			values(
				@ve_cod_guia_recepcion
				,@ve_cod_producto
				,@ve_nom_producto
				,@ve_cantidad
				,@ve_cod_item_doc
				,@ve_tipo_doc) 
		end 
	else if (@ve_operacion='UPDATE') 
		begin
			if (@ve_cantidad <> 0) -- si la cantidad es <> de cero hace update, sino borra el ítem 
				update item_guia_recepcion
				set cod_guia_recepcion	=	@ve_cod_guia_recepcion
					,cod_producto		=	@ve_cod_producto
					,nom_producto		=	@ve_nom_producto
					,cantidad			=	@ve_cantidad
					,cod_item_doc		=	@ve_cod_item_doc

				where cod_item_guia_recepcion	=	@ve_cod_item_guia_recepcion
			else	
				delete  item_guia_recepcion
	    		where cod_item_guia_recepcion  = @ve_cod_item_guia_recepcion			
		end	
	else if (@ve_operacion='DELETE_ALL') 
		begin
			delete  item_guia_recepcion
    		where cod_guia_recepcion = @ve_cod_guia_recepcion 
		end 
END
go