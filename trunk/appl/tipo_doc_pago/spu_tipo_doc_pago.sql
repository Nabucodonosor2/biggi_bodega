-------------------- spu_tipo_doc_pago ---------------------------------
ALTER PROCEDURE [dbo].[spu_tipo_doc_pago](@ve_operacion varchar(20)
											,@ve_cod_tipo_doc_pago numeric
											,@ve_nom_tipo_doc_pago varchar(100)=NULL
											,@ve_orden numeric=NULL
											,@ve_nom_corto varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into tipo_doc_pago (nom_tipo_doc_pago,orden,nom_corto)
		values (@ve_nom_tipo_doc_pago,@ve_orden,@ve_nom_corto)
	end 
	else if (@ve_operacion='RECALCULA') begin
			
		INSERT INTO ITEM_PROYECTO_INGRESO 
			(COD_PROYECTO_INGRESO
			,COD_TIPO_DOC_PAGO
			,COD_CUENTA_CONTABLE
			)
		select COD_PROYECTO_INGRESO
				,@ve_cod_tipo_doc_pago
				,null
		from proyecto_ingreso
	end	
	else if(@ve_operacion='UPDATE') begin
		update tipo_doc_pago 
		set nom_tipo_doc_pago = @ve_nom_tipo_doc_pago
			,orden = @ve_orden
			,nom_corto=@ve_nom_corto
	    where cod_tipo_doc_pago = @ve_cod_tipo_doc_pago
	end
	else if (@ve_operacion='DELETE') begin
		delete tipo_doc_pago 
    	where cod_tipo_doc_pago = @ve_cod_tipo_doc_pago
	end

	EXECUTE sp_orden_parametricas 'TIPO_DOC_PAGO'
END
GO