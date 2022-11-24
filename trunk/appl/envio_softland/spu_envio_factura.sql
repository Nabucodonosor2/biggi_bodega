----------------------------- spu_envio_factura ------------------------
create PROCEDURE spu_envio_factura(@ve_operacion				varchar(20)
								,@ve_cod_envio_factura		numeric
								,@ve_cod_envio_softland		numeric=null
								,@ve_seleccion_fa			varchar(1)=null
								,@ve_cod_factura			numeric=null)
AS
/*
Cuando la operacion es  'NRO_COMPROBANTE'
En @ve_cod_envio_softland viene el nro de comprobante
*/
BEGIN
	if (@ve_operacion='INSERT') begin
		if (@ve_seleccion_fa='S')
			insert into envio_factura 
				(cod_envio_softland
				,cod_factura)
			values 
				(@ve_cod_envio_softland
				,@ve_cod_factura)
	end 
	else if (@ve_operacion='UPDATE') begin
		if (@ve_seleccion_fa='N')
			delete envio_factura
			where cod_envio_factura = @ve_cod_envio_factura
	end
	else if (@ve_operacion='NRO_COMPROBANTE') begin
		update envio_factura
		set nro_comprobante = @ve_cod_envio_softland		-- equivalente @ve_nro_comprobante, se usa el mismo parametro para 2 opciones
		where cod_envio_factura = @ve_cod_envio_factura
		  and nro_comprobante is null -- actualiza solo si estaba VACIO
	end
END

