----------------------------- spu_envio_ncprov ------------------------
ALTER PROCEDURE [dbo].[spu_envio_ncprov](@ve_operacion						varchar(20)
										,@ve_cod_envio_ncprov		numeric
										,@ve_cod_envio_softland		numeric=null
										,@ve_seleccion_nc			varchar(1)=null
										,@ve_cod_ncprov				numeric=null)
AS
/*
Cuando la operacion es  'NRO_COMPROBANTE'
En @ve_cod_envio_softland viene el nro de comprobante
*/
BEGIN
	if (@ve_operacion='INSERT') begin
		if (@ve_seleccion_nc='S')
			insert into envio_ncprov
				(cod_envio_softland
				,cod_ncprov)
			values 
				(@ve_cod_envio_softland
				,@ve_cod_ncprov)
	end 
	else if (@ve_operacion='UPDATE') begin
		if (@ve_seleccion_nc='N')
			delete envio_ncprov
			where cod_envio_ncprov = @ve_cod_envio_ncprov
	end
	else if (@ve_operacion='NRO_COMPROBANTE') begin
		update envio_ncprov
		set nro_comprobante = @ve_cod_envio_softland		-- equivalente @ve_nro_comprobante, se usa el mismo parametro para 2 opciones
			,nro_correlativo_interno = @ve_cod_ncprov		-- equivalente @ve_nro_correlativo_interno, se usa el mismo parametro para 2 opciones
		where cod_envio_ncprov = @ve_cod_envio_ncprov
		  and nro_comprobante is null -- actualiza solo si estaba VACIO
	end
END



