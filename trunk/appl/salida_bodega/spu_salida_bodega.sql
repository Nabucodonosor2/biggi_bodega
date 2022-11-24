-------------------- spu_salida_bodega ---------------------------------
ALTER PROCEDURE [dbo].[spu_salida_bodega](@ve_operacion				varchar(20)
									,@ve_cod_salida_bodega		numeric
									,@ve_cod_usuario			numeric=null
									,@ve_cod_bodega				numeric=null
									,@ve_tipo_doc				varchar(100)=null
									,@ve_cod_doc				numeric=null
									,@ve_referencia				varchar(100)=null
									)
AS
BEGIN
	if (@ve_operacion='INSERT')
		insert into SALIDA_BODEGA
			(FECHA_SALIDA_BODEGA
			,COD_USUARIO
			,COD_BODEGA 
			,TIPO_DOC   
			,COD_DOC
			,REFERENCIA   
			)
		values
			(getdate()
			,@ve_cod_usuario			
			,@ve_cod_bodega				
			,@ve_tipo_doc				
			,@ve_cod_doc				
			,@ve_referencia
			)
	else if (@ve_operacion='UPDATE')
		update SALIDA_BODEGA
		set COD_USUARIO = @ve_cod_usuario
			,COD_BODEGA = @ve_cod_bodega
			,TIPO_DOC = @ve_tipo_doc
			,COD_DOC = @ve_cod_doc  
		where COD_SALIDA_BODEGA = @ve_cod_salida_bodega
	else if (@ve_operacion='DELETE') begin
		delete ITEM_SALIDA_BODEGA
		where COD_SALIDA_BODEGA = @ve_cod_salida_bodega

		delete SALIDA_BODEGA
		where COD_SALIDA_BODEGA = @ve_cod_salida_bodega
	end 
END
go