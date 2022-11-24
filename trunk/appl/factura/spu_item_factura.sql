-------------------- spu_item_factura ---------------------------------
ALTER PROCEDURE [dbo].[spu_item_factura](
								@ve_operacion varchar(20),
								@ve_cod_item_factura numeric,
								@ve_cod_factura numeric=NULL,
								@ve_orden numeric=NULL,
								@ve_item varchar(10)=NULL,
								@ve_cod_producto varchar(100)=NULL,
								@ve_nom_producto varchar(100)=NULL,
								@ve_cantidad T_CANTIDAD=NULL,
								@ve_precio T_PRECIO=NULL,
								@ve_cod_item_doc numeric=NULL,
								@ve_cod_tipo_te numeric=NULL,
								@ve_motivo_te varchar(100)=NULL,
								@ve_tipo_doc varchar(30)=NULL,
								@ve_cod_tipo_gas			numeric = NULL,
								@ve_cod_tipo_electricidad	numeric = NULL)

AS
BEGIN
	if (@ve_operacion='INSERT') 
		begin
			insert into item_factura(
				cod_factura,
				orden,
				item,
				cod_producto,
				nom_producto,
				cantidad,
				precio,
				cod_item_doc,
				cod_tipo_te,
				motivo_te,
				tipo_doc,
				cod_tipo_gas,
				cod_tipo_electricidad)
			values		(
				@ve_cod_factura,
				@ve_orden,
				@ve_item,
				@ve_cod_producto,
				@ve_nom_producto,
				@ve_cantidad,
				@ve_precio,
				@ve_cod_item_doc,
				@ve_cod_tipo_te,
				@ve_motivo_te,
				@ve_tipo_doc,
				@ve_cod_tipo_gas,
				@ve_cod_tipo_electricidad) 
		end 

	else if (@ve_operacion='UPDATE') 
		begin
			if (@ve_cantidad <> 0) -- si la cantidad es <> de cero hace update, sino borra el ítem
				update item_factura
				set cod_factura			=	@ve_cod_factura,
					orden				=	@ve_orden,
					item				=	@ve_item,
					cod_producto		=	@ve_cod_producto,
					nom_producto		=	@ve_nom_producto,
					cantidad			=	@ve_cantidad,
					precio				=	@ve_precio,
					cod_item_doc		=	@ve_cod_item_doc,
					cod_tipo_te			=	@ve_cod_tipo_te,
					motivo_te			=	@ve_motivo_te,
					cod_tipo_gas		=	@ve_cod_tipo_gas,
					cod_tipo_electricidad =	@ve_cod_tipo_electricidad
				where cod_item_factura  =	@ve_cod_item_factura
			else	
				delete  item_factura 
	    		where cod_item_factura  = @ve_cod_item_factura			
		end	
	else if (@ve_operacion='DELETE') 
		begin
			delete  item_factura 
    		where cod_item_factura = @ve_cod_item_factura
		end 
END
go
