----------------------------- spu_envio_empresa ------------------------
create PROCEDURE spu_envio_empresa(@ve_operacion				varchar(20)
									,@ve_cod_envio_empresa		numeric
									,@ve_cod_envio_softland		numeric=null
									,@ve_seleccion				varchar(1)=null
									,@ve_cod_cod_empresa		numeric=null)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		if (@ve_seleccion='S')
			insert into envio_empresa
				(cod_envio_softland
				,cod_empresa)
			values 
				(@ve_cod_envio_softland
				,@ve_cod_cod_empresa)
	end 
	else if (@ve_operacion='UPDATE') begin
		if (@ve_seleccion='N')
			delete envio_empresa
			where cod_envio_empresa = @ve_cod_envio_empresa
	end
END
