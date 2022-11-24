----------------------------- spu_envio_softland ------------------------
ALTER PROCEDURE [dbo].[spu_envio_softland](@ve_operacion				varchar(20)
								,@ve_cod_envio_softland		numeric
								,@ve_cod_usuario			numeric=null
								,@ve_cod_tipo_envio			numeric=null
								,@ve_nro_comprobante		numeric=null
								,@ve_cod_estado_envio		numeric=null
								,@ve_nro_correlativo_interno numeric=null)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into envio_softland
			(fecha_envio_softland
			,cod_usuario
			,cod_tipo_envio
			,nro_comprobante
			,cod_estado_envio
			,nro_correlativo_interno)
		values 
			(getdate()
			,@ve_cod_usuario
			,@ve_cod_tipo_envio
			,@ve_nro_comprobante
			,@ve_cod_estado_envio
			,@ve_nro_correlativo_interno)
	end 
	else if (@ve_operacion='UPDATE') begin
		update envio_softland
		set cod_tipo_envio = @ve_cod_tipo_envio
			,nro_comprobante = @ve_nro_comprobante
			,cod_estado_envio = @ve_cod_estado_envio
			,nro_correlativo_interno = @ve_nro_correlativo_interno
		where cod_envio_softland = @ve_cod_envio_softland

		-- confirmar
	end
	else if (@ve_operacion='DELETE') begin
		delete envio_empresa
		where cod_envio_softland = @ve_cod_envio_softland

		delete envio_factura
		where cod_envio_softland = @ve_cod_envio_softland

		delete envio_nota_credito
		where cod_envio_softland = @ve_cod_envio_softland

		delete envio_softland
		where cod_envio_softland = @ve_cod_envio_softland
	end
END

