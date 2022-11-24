-------------------------- spu_deposito ------------------------------
create PROCEDURE spu_deposito(@ve_operacion				varchar(20)
							, @ve_cod_deposito			numeric
							, @ve_cod_usuario			numeric=NULL
							, @ve_nro_deposito			numeric=NULL
							, @ve_cod_cuenta_corriente	numeric=NULL
							, @ve_cod_estado_deposito	numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into deposito 
			(fecha_deposito
			,cod_usuario
			,nro_deposito
			,cod_cuenta_corriente
			,cod_estado_deposito)
		values
			(getdate()
			,@ve_cod_usuario
			,@ve_nro_deposito
			,@ve_cod_cuenta_corriente
			,@ve_cod_estado_deposito)
	end
	if (@ve_operacion='UPDATE') begin
		update deposito 
		set nro_deposito = @ve_nro_deposito
			,cod_cuenta_corriente = @ve_cod_cuenta_corriente
			,cod_estado_deposito = @ve_cod_estado_deposito
		where cod_deposito = @ve_cod_deposito
	end
	else if (@ve_operacion='DELETE') begin
		delete item_deposito 
		where cod_deposito = @ve_cod_deposito

		delete deposito 
		where cod_deposito = @ve_cod_deposito
	end		
END
