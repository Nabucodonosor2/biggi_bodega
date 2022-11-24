-------------------- spu_centro_costo_empresa ----------------------------------
CREATE PROCEDURE [dbo].[spu_centro_costo_empresa](
								@ve_operacion varchar(20),
								@ve_cod_centro_costo_empresa numeric,
								@ve_cod_centro_costo varchar(30)=NULL,
								@ve_cod_empresa numeric=NULL)
AS
BEGIN
	declare	@count		numeric

	if (@ve_operacion='INSERT') 
		begin
			SELECT	@count = count(*)
			FROM	centro_costo_empresa 
			WHERE	cod_empresa = @ve_cod_empresa
			AND		cod_centro_costo = @ve_cod_centro_costo

			if (@count > 0) begin
				SELECT @ve_cod_centro_costo_empresa = cod_centro_costo_empresa
				FROM CENTRO_COSTO_EMPRESA
				WHERE	COD_EMPRESA = @ve_cod_empresa
				AND	COD_CENTRO_COSTO = @ve_cod_centro_costo
	
				set @ve_operacion='UPDATE'
			end 
			else begin
				INSERT INTO CENTRO_COSTO_EMPRESA
					(COD_CENTRO_COSTO ,COD_EMPRESA)
				values
					(@ve_cod_centro_costo, @ve_cod_empresa)
			end 
		end 

	else if (@ve_operacion='UPDATE') 
		begin
			if (@ve_cod_empresa <> 0) -- si la empresa es <> de cero hace update, sino borra la empresa de centro_costo
				update centro_costo_empresa
				set cod_centro_costo	=	@ve_cod_centro_costo,
					cod_empresa			=	@ve_cod_empresa
				where cod_centro_costo_empresa = @ve_cod_centro_costo_empresa
			else	
				delete  centro_costo_empresa 
	    		where cod_centro_costo_empresa = @ve_cod_centro_costo_empresa			
		end	
	else if (@ve_operacion='DELETE') 
		begin
			delete  centro_costo_empresa 
    		where cod_centro_costo_empresa = @ve_cod_centro_costo_empresa
		end 
END
go