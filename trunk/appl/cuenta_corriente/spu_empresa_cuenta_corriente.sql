-------------------- spu_empresa_cuenta_corriente  ---------------------------------
create PROCEDURE spu_empresa_cuenta_corriente
(
	@ve_operacion varchar(20), 
	@ve_cod_empresa_cuenta_corriente numeric, 
	@ve_cod_empresa numeric=NULL, 
	@ve_cod_cuenta_corriente numeric=NULL
)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into empresa_cuenta_corriente (cod_empresa, cod_cuenta_corriente)
		values (@ve_cod_empresa, @ve_cod_cuenta_corriente)
	end 
	else if (@ve_operacion='UPDATE') begin
		update empresa_cuenta_corriente
		set cod_empresa = @ve_cod_empresa, 
			cod_cuenta_corriente = @ve_cod_cuenta_corriente
		where cod_empresa_cuenta_corriente = @ve_cod_empresa_cuenta_corriente
	end
	else if (@ve_operacion='DELETE') begin
		delete empresa_cuenta_corriente
    	where cod_empresa_cuenta_corriente = @ve_cod_empresa_cuenta_corriente
	end
END
go