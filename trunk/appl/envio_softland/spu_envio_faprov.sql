----------------------------- spu_envio_faprov ------------------------
ALTER PROCEDURE [dbo].[spu_envio_faprov](@ve_operacion				varchar(20)
								,@ve_cod_envio_faprov		numeric
								,@ve_cod_envio_softland		numeric=null
								,@ve_seleccion_fa			varchar(1)=null
								,@ve_cod_faprov				numeric=null)
AS
/*
Cuando la operacion es  'NRO_COMPROBANTE'
En @ve_cod_envio_softland viene el nro de comprobante
*/
BEGIN
	if (@ve_operacion='INSERT') begin
		if (@ve_seleccion_fa='S')
			insert into envio_faprov
				(cod_envio_softland
				,cod_faprov)
			values 
				(@ve_cod_envio_softland
				,@ve_cod_faprov)
	end 
	else if (@ve_operacion='UPDATE') begin
		if (@ve_seleccion_fa='N')
			delete envio_faprov
			where cod_envio_faprov = @ve_cod_envio_faprov
	end
	else if (@ve_operacion='NRO_COMPROBANTE') begin
		update envio_faprov
		set nro_comprobante = @ve_cod_envio_softland		-- equivalente @ve_nro_comprobante, se usa el mismo parametro para 2 opciones
			,nro_correlativo_interno = @ve_cod_faprov		-- equivalente @ve_nro_correlativo_interno, se usa el mismo parametro para 2 opciones
		where cod_envio_faprov = @ve_cod_envio_faprov
		  and nro_comprobante is null -- actualiza solo si estaba VACIO
	end
END