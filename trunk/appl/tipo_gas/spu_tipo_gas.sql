------------------  spu_tipo_gas  --------------------------
CREATE PROCEDURE [dbo].[spu_tipo_gas](@ve_operacion varchar(20),@ve_cod_tipo_gas numeric,@ve_nom_tipo_gas varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into tipo_gas (nom_tipo_gas,orden)
		values (@ve_nom_tipo_gas, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update	tipo_gas
		set		nom_tipo_gas	= @ve_nom_tipo_gas,			
				orden					= @ve_orden
		where	cod_tipo_gas	= @ve_cod_tipo_gas;
	end
	else if (@ve_operacion='DELETE') begin
		delete tipo_gas 
    	where cod_tipo_gas = @ve_cod_tipo_gas
	end

	EXECUTE sp_orden_parametricas 'TIPO_GAS'
END
go