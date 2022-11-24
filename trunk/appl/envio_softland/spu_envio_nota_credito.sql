----------------------------- spu_envio_nota_credito ------------------------
create PROCEDURE spu_envio_nota_credito(@ve_operacion					varchar(20)
										,@ve_cod_envio_nota_credito		numeric
										,@ve_cod_envio_softland			numeric=null
										,@ve_seleccion_nc				varchar(1)=null
										,@ve_cod_nota_credito			numeric=null)
AS
/*
Cuando la operacion es  'NRO_COMPROBANTE'
En @ve_cod_envio_softland viene el nro de comprobante
*/
BEGIN
	if (@ve_operacion='INSERT') begin
		if (@ve_seleccion_nc='S')
			insert into envio_nota_credito
				(cod_envio_softland
				,cod_nota_credito)
			values 
				(@ve_cod_envio_softland
				,@ve_cod_nota_credito)
	end 
	else if (@ve_operacion='UPDATE') begin
		if (@ve_seleccion_nc='N')
			delete envio_nota_credito
			where cod_envio_nota_credito = @ve_cod_envio_nota_credito
	end
	else if (@ve_operacion='NRO_COMPROBANTE') begin
		update envio_nota_credito
		set nro_comprobante = @ve_cod_envio_softland		-- equivalente @ve_nro_comprobante, se usa el mismo parametro para 2 opciones
		where cod_envio_nota_credito = @ve_cod_envio_nota_credito
		  and nro_comprobante is null -- actualiza solo si estaba VACIO
	end
END

