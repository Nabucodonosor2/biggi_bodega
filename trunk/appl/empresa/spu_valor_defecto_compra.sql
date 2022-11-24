------------------  spu_valor_defecto_compra  ----------------------------
CREATE PROCEDURE [dbo].[spu_valor_defecto_compra](@ve_operacion varchar(20), @ve_cod_empresa numeric, @ve_cod_persona numeric=NULL, @ve_cod_forma_pago numeric=NULL)
AS
BEGIN	
	if(@ve_operacion='INSERT')begin	
		delete valor_defecto_compra
		where cod_empresa = @ve_cod_empresa
		
		insert into valor_defecto_compra (cod_empresa, cod_persona, cod_forma_pago)
		values (@ve_cod_empresa, @ve_cod_persona, @ve_cod_forma_pago)
	end 	
	else if (@ve_operacion='DELETE') begin
		delete valor_defecto_compra
		where cod_empresa = @ve_cod_empresa
	end
END