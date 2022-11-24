-------------------- spu_item_proyecto_ingreso ---------------------------------
CREATE PROCEDURE [dbo].[spu_item_proyecto_ingreso](
		@ve_operacion varchar(20),
		@ve_cod_item_proyecto_ingreso numeric,
		@ve_cod_proyecto_ingreso numeric=NULL,
		@ve_cod_tipo_doc_pago numeric=NULL,
		@ve_cod_cuenta_contable  numeric=NULL
		)
	
AS
	declare @precio_old numeric,
			@cod_item_cotizacion numeric
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into item_proyecto_ingreso(
					cod_proyecto_ingreso,
					cod_tipo_doc_pago,
					cod_cuenta_contable)
		values		(
					@ve_cod_proyecto_ingreso,
					@ve_cod_tipo_doc_pago,
					@ve_cod_cuenta_contable
					) 
	end
	else if (@ve_operacion='UPDATE') begin
		
		update item_proyecto_ingreso
		set cod_proyecto_ingreso		=	@ve_cod_proyecto_ingreso,
			cod_tipo_doc_pago			=	@ve_cod_tipo_doc_pago,
			cod_cuenta_contable			=	@ve_cod_cuenta_contable   
		where cod_item_proyecto_ingreso	=	@ve_cod_item_proyecto_ingreso
	end
	else if (@ve_operacion='DELETE') begin
		delete  item_proyecto_ingreso 
	    where cod_item_proyecto_ingreso = @ve_cod_item_proyecto_ingreso
	end	
END
go