----------------------------- spu_envio_pago_faprov ------------------------
create PROCEDURE spu_envio_pago_faprov(@ve_operacion				varchar(20)
								,@ve_cod_envio_pago_faprov		numeric
								,@ve_cod_envio_softland			numeric=null
								,@ve_seleccion					varchar(1)=null
								,@ve_cod_pago_faprov			numeric=null)
AS
/*
Cuando la operacion es  'NRO_COMPROBANTE'
En @ve_cod_envio_softland viene el nro de comprobante
*/
BEGIN
	if (@ve_operacion='INSERT') begin
		if (@ve_seleccion='S')
			insert into envio_pago_faprov
				(cod_envio_softland
				,cod_pago_faprov)
			values 
				(@ve_cod_envio_softland
				,@ve_cod_pago_faprov)
	end 
	else if (@ve_operacion='UPDATE') begin
		if (@ve_seleccion='N')
			delete envio_pago_faprov
			where cod_envio_pago_faprov = @ve_cod_envio_pago_faprov
	end
	else if (@ve_operacion='NRO_COMPROBANTE') begin
		update envio_pago_faprov
		set nro_comprobante = @ve_cod_envio_softland		-- equivalente @ve_nro_comprobante, se usa el mismo parametro para 2 opciones
			,nro_correlativo_interno = @ve_cod_pago_faprov	-- equivalente @ve_nro_correlativo_interno, se usa el mismo parametro para 2 opciones
		where cod_envio_pago_faprov = @ve_cod_envio_pago_faprov
		  and nro_comprobante is null -- actualiza solo si estaba VACIO
	end
END