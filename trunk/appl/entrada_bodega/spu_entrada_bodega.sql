-------------------- spu_entrada_bodega ---------------------------------
alter  PROCEDURE spu_entrada_bodega(@ve_operacion				varchar(20)
									,@ve_cod_entrada_bodega		numeric=null
									,@ve_cod_usuario			numeric=null
									,@ve_cod_bodega				numeric=null
									,@ve_tipo_doc				varchar(100)=null
									,@ve_cod_doc				numeric=null
									,@ve_referencia				varchar(100)=null
									)
AS
BEGIN
	if (@ve_operacion='INSERT')
		insert into ENTRADA_BODEGA
			(FECHA_ENTRADA_BODEGA
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
		update ENTRADA_BODEGA
		set COD_USUARIO = @ve_cod_usuario
			,COD_BODEGA = @ve_cod_bodega
			,TIPO_DOC = @ve_tipo_doc
			,COD_DOC = @ve_cod_doc
			,REFERENCIA = @ve_referencia
		where COD_ENTRADA_BODEGA = @ve_cod_entrada_bodega
	else if (@ve_operacion='DELETE') begin
		delete ITEM_ENTRADA_BODEGA
		where COD_ENTRADA_BODEGA = @ve_cod_entrada_bodega

		delete ENTRADA_BODEGA
		where COD_ENTRADA_BODEGA = @ve_cod_entrada_bodega
	end 
END