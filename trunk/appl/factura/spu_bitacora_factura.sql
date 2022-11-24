----------------------- spu_bitacora_factura -------------------------------
create PROCEDURE spu_bitacora_factura(@ve_operacion				varchar(20)
									, @ve_cod_bitacora_factura	numeric
									, @ve_cod_usuario			numeric=NULL
									, @ve_cod_factura			numeric=NULL
									, @ve_cod_accion_cobranza	numeric=NULL
									, @ve_contacto				varchar(100)=NULL
									, @ve_telefono				varchar(100)=NULL
									, @ve_mail					varchar(100)=NULL
									, @ve_glosa					varchar(100)=NULL
									, @ve_tiene_compromiso		varchar(1)=NULL
									, @ve_fecha_compromiso		datetime=NULL
									, @ve_glosa_compromiso		varchar(100)=NULL
									, @ve_compromiso_realizado		varchar(1)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into BITACORA_FACTURA
			(FECHA_BITACORA_FACTURA
			,COD_USUARIO
			,COD_FACTURA
			,COD_ACCION_COBRANZA
			,CONTACTO
			,TELEFONO
			,MAIL
			,GLOSA
			,TIENE_COMPROMISO
			,FECHA_COMPROMISO
			,GLOSA_COMPROMISO
			,COMPROMISO_REALIZADO
			,FECHA_REALIZADO
			,COD_USUARIO_REALIZADO)
		values
			(getdate()
			,@ve_cod_usuario
			,@ve_cod_factura
			,@ve_cod_accion_cobranza
			,@ve_contacto
			,@ve_telefono
			,@ve_mail
			,@ve_glosa
			,@ve_tiene_compromiso
			,@ve_fecha_compromiso
			,@ve_glosa_compromiso
			,null
			,null
			,null)
	end 
	if (@ve_operacion='UPDATE') begin
		declare
			@fecha_realizado		datetime
			,@cod_usuario_realizado	numeric

		if (@ve_compromiso_realizado = 'S') begin
			set @fecha_realizado = getdate()
			set @cod_usuario_realizado = @ve_cod_usuario
		end 
		else begin
			set @fecha_realizado = null
			set @cod_usuario_realizado = null
		end 
		update BITACORA_FACTURA 
		set COD_ACCION_COBRANZA = @ve_cod_accion_cobranza
			,CONTACTO = @ve_contacto
			,TELEFONO = @ve_telefono
			,MAIL = @ve_mail
			,GLOSA = @ve_glosa
			,TIENE_COMPROMISO = @ve_tiene_compromiso
			,FECHA_COMPROMISO = @ve_fecha_compromiso
			,GLOSA_COMPROMISO = @ve_glosa_compromiso
			,COMPROMISO_REALIZADO = @ve_compromiso_realizado
			,FECHA_REALIZADO = @fecha_realizado
			,COD_USUARIO_REALIZADO = @cod_usuario_realizado
	    where COD_BITACORA_FACTURA = @ve_cod_bitacora_factura
	end
	else if (@ve_operacion='DELETE') begin
		delete BITACORA_FACTURA 
    	where COD_BITACORA_FACTURA = @ve_cod_bitacora_factura
	end
END

