-------------------- spu_item_guia_despacho ---------------------------------
-- creado por CS el 02/06/09 
-- permite insertar y modificar datos en los item de Guia de Despacho
-------------------- spu_item_guia_despacho ---------------------------------
CREATE PROCEDURE [dbo].[spu_item_guia_despacho](
								@ve_operacion varchar(20),
								@ve_cod_item_guia_despacho numeric,
								@ve_cod_guia_despacho numeric=NULL,
								@ve_orden numeric=NULL,
								@ve_item varchar(10)=NULL,
								@ve_cod_producto varchar(100)=NULL,
								@ve_nom_producto varchar(100)=NULL,
								@ve_cantidad T_CANTIDAD=NULL,
								@ve_precio T_PRECIO=NULL,
								@ve_cod_item_doc numeric=NULL,
								@ve_cod_tipo_te numeric=NULL,
								@ve_motivo_te varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') 
		begin
			insert into item_guia_despacho(
				cod_guia_despacho,
				orden,
				item,
				cod_producto,
				nom_producto,
				cantidad,
				precio,
				cod_item_doc,
				cod_tipo_te,
				motivo_te)
			values(
				@ve_cod_guia_despacho,
				@ve_orden,
				@ve_item,
				@ve_cod_producto,
				@ve_nom_producto,
				@ve_cantidad,
				@ve_precio,
				@ve_cod_item_doc,
				@ve_cod_tipo_te,
				@ve_motivo_te) 
		end 

	else if (@ve_operacion='UPDATE') 
		begin
			if (@ve_cantidad <> 0) -- si la cantidad es <> de cero hace update, sino borra el ítem
				update item_guia_despacho
				set cod_guia_despacho	=	@ve_cod_guia_despacho,
					orden				=	@ve_orden,
					item				=	@ve_item,
					cod_producto		=	@ve_cod_producto,
					nom_producto		=	@ve_nom_producto,
					cantidad			=	@ve_cantidad,
					precio				=	@ve_precio,
					cod_item_doc		=	@ve_cod_item_doc,
					cod_tipo_te			=	@ve_cod_tipo_te,
					motivo_te			=	@ve_motivo_te
				where cod_item_guia_despacho=	@ve_cod_item_guia_despacho
			else	
				delete  item_guia_despacho 
	    		where cod_item_guia_despacho = @ve_cod_item_guia_despacho			
		end	
	else if (@ve_operacion='DELETE') 
		begin
			delete  item_guia_despacho 
    		where cod_item_guia_despacho = @ve_cod_item_guia_despacho
		end 
END
go